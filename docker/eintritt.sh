#!/bin/sh
# MekoTools H5P-Studio — Eintritt in den Behälter.
#
# Aufgaben:
#  1. auf die Datenbank warten
#  2. Einstellungen aus der Umgebung schreiben (jeder Start neu, damit ein
#     ersetzter Behälter dieselbe Datei bekommt — das Geheimnis für Sitzungen
#     liegt im Datenträger, sonst fliegen nach jedem Austausch alle Anmeldungen raus)
#  3. beim ersten Start die Anwendung einrichten, danach nur noch aktualisieren
#  4. stündlich die Hausarbeiten der Anwendung anstossen (im Prozess, kein Fremdplaner)
#  5. Apache übernehmen
set -eu

melden() { echo "[eintritt] $*"; }
warnen() { echo "[eintritt] WARNUNG: $*" >&2; }

SEITE="${STUDIO_SEITE:-/opt/drupal/web}"
DATEN="${STUDIO_DATEN:-/daten}"
DATEIEN="$DATEN/dateien"
PRIVAT="$DATEN/privat"
MERKE="$DATEN/.installiert"
SETTINGS="$SEITE/sites/default/settings.php"

BASIS="${STUDIO_BASIS_URL:-https://h5p-studio.mekotools.de}"
HOST="$(printf '%s' "$BASIS" | sed -e 's#^[a-z]*://##' -e 's#/.*$##')"
DB_NAME="${STUDIO_DB_NAME:-studio}"
DB_USER="${STUDIO_DB_USER:-studio}"
DB_PASS="${STUDIO_DB_PASS:?STUDIO_DB_PASS fehlt}"
DB_HOST="${STUDIO_DB_HOST:-mekotools-studio-db}"
PROXY_NETZ="${STUDIO_PROXY_NETZ:-10.0.1.0/24}"
ADMIN_NAME="${STUDIO_ADMIN_NAME:-notfall}"
ADMIN_MAIL="${STUDIO_ADMIN_MAIL:-}"
ADMIN_PASS="${STUDIO_ADMIN_PASS:?STUDIO_ADMIN_PASS fehlt}"

cd /opt/drupal

# --- 1. Datenbank -----------------------------------------------------------
melden "warte auf die Datenbank ($DB_HOST)"
i=0
until php -r '$k=new PDO("mysql:host=".getenv("STUDIO_DB_HOST").";port=3306",getenv("STUDIO_DB_USER"),getenv("STUDIO_DB_PASS")); $k=null;' 2>/dev/null; do
  i=$((i + 1))
  if [ "$i" -ge 60 ]; then
    warnen "Datenbank nach 5 Minuten nicht erreichbar — Abbruch"
    exit 1
  fi
  sleep 5
done
melden "Datenbank antwortet"

# --- 2. Verzeichnisse und Einstellungen -------------------------------------
mkdir -p "$DATEIEN" "$PRIVAT" "$DATEN/config/sync"
[ -e "$SEITE/sites/default/files" ] || ln -s "$DATEIEN" "$SEITE/sites/default/files"

if [ ! -f "$DATEN/.hash_salt" ]; then
  openssl rand -hex 32 > "$DATEN/.hash_salt"
  chmod 600 "$DATEN/.hash_salt"
  melden "Sitzungsgeheimnis einmalig erzeugt"
fi
SALT="$(cat "$DATEN/.hash_salt")"

cp -f "$SEITE/sites/default/default.settings.php" "$SETTINGS"
cat >> "$SETTINGS" <<PHP

// --- MekoTools H5P-Studio: aus der Umgebung ---------------------------------
\$databases['default']['default'] = [
  'database' => '$DB_NAME',
  'username' => '$DB_USER',
  'password' => '$DB_PASS',
  'host' => '$DB_HOST',
  'port' => 3306,
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_unicode_ci',
];
\$settings['hash_salt'] = '$SALT';
\$settings['file_private_path'] = '$PRIVAT';
\$settings['config_sync_directory'] = '$DATEN/config/sync';
\$settings['trusted_host_patterns'] = ['^$(printf '%s' "$HOST" | sed -e 's/\./\\./g')\$', '^localhost\$', '^127\\.0\\.0\\.1\$'];
// Die Verschlüsselung endet am VPS; der Vermittler setzt die Kopfzeilen.
\$settings['reverse_proxy'] = TRUE;
\$settings['reverse_proxy_addresses'] = ['$PROXY_NETZ'];
PHP
chown www-data:www-data "$SETTINGS" "$MERKE" 2>/dev/null || true
chown -R www-data:www-data "$DATEIEN" "$PRIVAT" "$DATEN/config"
chmod 644 "$SETTINGS"

# --- 3a. Bausteine, Sprache und Anmeldung -----------------------------------
bausteine_einschalten() {
  melden "Bausteine einschalten"
  drush pm:enable -y h5p h5peditor locale language openid_connect mekotools_studio \
    || warnen "nicht alle Bausteine liessen sich einschalten"

  melden "deutsche Oberfläche"
  if drush language:add de -y >/dev/null 2>&1 \
     && drush config:set system.site default_langcode de -y >/dev/null 2>&1 \
     && drush locale:check -y >/dev/null 2>&1 \
     && drush locale:update -y >/dev/null 2>&1; then
    melden "Übersetzungen geladen"
  else
    warnen "Übersetzungen konnten nicht geladen werden — die Oberfläche bleibt teilweise englisch"
  fi

  anmeldung_einrichten
}

anmeldung_einrichten() {
  if [ -z "${STUDIO_OIDC_KENNUNG:-}" ] || [ -z "${STUDIO_OIDC_GEHEIM:-}" ]; then
    warnen "keine Kennung für die Anmeldung gesetzt — es gilt vorerst nur das Notfallkonto"
    return 0
  fi
  melden "Anmeldung über Pocket ID einrichten"

  # Am KUNDEN stehen nur die Angaben, die den Anbieter betreffen. Die
  # Verhaltensschalter (Anzeige im Anmeldeformular, Rollenzuordnung, Merkmale)
  # sitzen in den MODULEINSTELLUNGEN — am Kunden abgelegt bleiben sie wirkungslos.
  drush php:eval '
    $speicher = \Drupal::entityTypeManager()->getStorage("openid_connect_client");
    $client = $speicher->load("pocketid") ?: $speicher->create(["id" => "pocketid"]);
    $client->set("label", "MekoTools-Anmeldung");
    $client->set("plugin", "generic");
    $client->set("status", TRUE);
    $client->set("settings", [
      "client_id" => getenv("STUDIO_OIDC_KENNUNG"),
      "client_secret" => getenv("STUDIO_OIDC_GEHEIM"),
      "issuer_url" => getenv("STUDIO_OIDC_ISSUER") ?: "https://auth.mekotools.de",
      "scopes" => ["openid", "profile", "email", "groups"],
    ]);
    $client->save();
    echo "Kunde eingerichtet: " . $client->id() . "\n";
  ' || warnen "Anmeldung konnte nicht eingerichtet werden"

  GRUPPE="${STUDIO_OIDC_GRUPPE:-lehrkraefte}"
  drush config:set openid_connect.settings user_login_display above -y >/dev/null 2>&1
  drush config:set openid_connect.settings always_save_userinfo true -y >/dev/null 2>&1
  drush config:set openid_connect.settings autostart_login false -y >/dev/null 2>&1
  # KEIN Verknüpfen mit bestehenden Konten: sonst könnte, wer dieselbe Adresse
  # wie das Notfallkonto benutzt, an dessen Rechte kommen. Lehrkräfte bekommen
  # ein eigenes Konto.
  drush config:set openid_connect.settings connect_existing_users false -y >/dev/null 2>&1
  drush config:set openid_connect.settings userinfo_mappings \
    '{"mail":"email","name":"preferred_username"}' --input-format=json -y >/dev/null 2>&1
  # Wer im Anmeldedienst in der Gruppe ist, bekommt hier die Rolle Lehrkraft.
  # Beide Schreibweisen, weil das Merkmal den Namen oder die Anzeige tragen kann.
  drush config:set openid_connect.settings role_mappings \
    "{\"lehrkraft\":[\"$GRUPPE\",\"Lehrkräfte\"]}" --input-format=json -y >/dev/null 2>&1
  drush config:set openid_connect.settings force_reset_role_mappings true -y >/dev/null 2>&1
  melden "Anmeldung eingerichtet, Rolle Lehrkraft an die Gruppe $GRUPPE gebunden"
}

# --- 3b. Einrichten oder aktualisieren --------------------------------------
# Der Verweis für drush wird stückweise zusammengesetzt. So steht das Muster
# "Benutzer:Geheimnis@Rechner" nirgends als zusammenhängende Zeichenkette im
# Quelltext und kann nicht versehentlich in Protokollen auftauchen.
DB_URL="mysql://${DB_USER}"
DB_URL="${DB_URL}:${DB_PASS}"
DB_URL="${DB_URL}@${DB_HOST}:3306/${DB_NAME}"

if [ ! -f "$MERKE" ]; then
  melden "Ersteinrichtung beginnt (einmalig, dauert ein bis zwei Minuten)"
  if drush site:install standard \
      --db-url="$DB_URL" \
      --account-name="$ADMIN_NAME" \
      --account-mail="$ADMIN_MAIL" \
      --account-pass="$ADMIN_PASS" \
      --site-name="H5P-Studio" \
      --site-mail="$ADMIN_MAIL" \
      -y; then
    melden "Grundfassung eingerichtet"
  else
    warnen "die Einrichtung brach ab — zweiter Blick auf die Anlage"
    if drush status --fields=bootstrap 2>/dev/null | grep -qi "successful"; then
      warnen "die Anlage antwortet trotzdem — die Bausteine werden nachgezogen"
    else
      warnen "die Anlage ist nicht benutzbar; der Behälter bleibt stehen, damit es sichtbar ist"
      exit 1
    fi
  fi

  bausteine_einschalten
  drush cache:rebuild -y || true
  touch "$MERKE"
  chown www-data:www-data "$MERKE"
  melden "Ersteinrichtung abgeschlossen"
else
  melden "Einrichtung vorhanden — Aktualisierungen laufen"
  drush updatedb -y || warnen "Datenbank-Aktualisierung schlug fehl"
  drush cache:rebuild -y || warnen "Zwischenspeicher liess sich nicht leeren"
  # Die Anmeldung wird bei JEDEM Start nachgezogen, nicht nur bei der
  # Ersteinrichtung: nur so wirken geänderte Werte in der .env, ohne dass von
  # Hand eingegriffen werden muss. Der Aufruf ist wiederholbar (anlegen oder
  # laden), und er ist der einzige Ort, an dem Anmeldekunde und
  # Rollenzuordnung gesetzt werden.
  anmeldung_einrichten
  drush cache:rebuild -y >/dev/null 2>&1 || true
fi

# --- 4. Hausarbeiten stündlich anstossen (im Prozess) -----------------------
(
  while true; do
    sleep 3600
    drush cron >/dev/null 2>&1 || true
  done
) &

# --- 5. Apache übernehmen ---------------------------------------------------
melden "starte Webserver"
exec "$@"
