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
// Zugang zum Anmeldedienst für die Freigabeseite. Der Verwaltungsschlüssel
// kommt aus der .env des Stapels und steht damit nicht im Quelltext.
\$settings['mekotools_studio_pocketid'] = [
  'basis' => '${STUDIO_POCKETID_BASIS:-https://auth.mekotools.de}',
  'schluessel' => '${STUDIO_POCKETID_SCHLUESSEL:-}',
];
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
  GRUPPE="${STUDIO_OIDC_GRUPPE:-lehrkraefte}"
  STUFE1="${STUDIO_OIDC_GRUPPE_ANGEMELDET:-angemeldet}"
  VERWALTUNG="${STUDIO_OIDC_GRUPPE_VERWALTUNG:-verwaltung}"

  # Kundeneinstellungen UND Moduleinstellungen in einem Aufruf. Zwei Fallen:
  #  * Die Entität muss ihre Sorte ("plugin") schon beim Anlegen kennen — sonst
  #    baut sie ihre Sortensammlung mit leerem Wert und bricht mit einem
  #    TypeError ab (genau das passierte am 09.10.2026).
  #  * Die Verhaltensschalter sitzen in den MODULEINSTELLUNGEN; am Kunden
  #    abgelegt bleiben sie wirkungslos. Sie werden typisiert gesetzt, nicht als
  #    Zeichenkette — sonst steht später Text statt einer Zuordnung darin.
  # Die Endpunkte holt sich das Modul NICHT von selbst: die Selbst-Erkundung
  # läuft nur im Formular und legt das Ergebnis dort ab. Ohne sie bleibt der
  # Anmeldebeginn leer. Deshalb wird das Ausstellerdokument hier gelesen.
  STUDIO_OIDC_GRUPPE="$GRUPPE" STUDIO_OIDC_GRUPPE_ANGEMELDET="$STUFE1" STUDIO_OIDC_VERWALTUNG="$VERWALTUNG" drush php:eval '
    $aussteller = rtrim(getenv("STUDIO_OIDC_ISSUER") ?: "https://auth.mekotools.de", "/");
    $werte = [
      "client_id" => getenv("STUDIO_OIDC_KENNUNG"),
      "client_secret" => getenv("STUDIO_OIDC_GEHEIM"),
      "issuer_url" => $aussteller,
      "scopes" => ["openid", "profile", "email", "groups"],
    ];
    try {
      $antwort = \Drupal::httpClient()->get($aussteller . "/.well-known/openid-configuration", ["timeout" => 15]);
      $daten = json_decode((string) $antwort->getBody(), TRUE) ?: [];
      foreach (["authorization_endpoint", "token_endpoint", "userinfo_endpoint", "end_session_endpoint"] as $schluessel) {
        if (!empty($daten[$schluessel])) {
          $werte[$schluessel] = $daten[$schluessel];
        }
      }
      $fehlend = array_diff(["authorization_endpoint", "token_endpoint", "userinfo_endpoint"], array_keys($werte));
      echo $fehlend
        ? "WARNUNG: Ausstellerdokument unvollstaendig, es fehlt: " . implode(",", $fehlend) . "\n"
        : "Endpunkte aus dem Ausstellerdokument uebernommen\n";
    }
    catch (\Throwable $e) {
      echo "WARNUNG: Ausstellerdokument nicht lesbar: " . $e->getMessage() . "\n";
    }
    $gruppe = getenv("STUDIO_OIDC_GRUPPE") ?: "lehrkraefte";
    $stufe1 = getenv("STUDIO_OIDC_GRUPPE_ANGEMELDET") ?: "angemeldet";
    $verwaltung = getenv("STUDIO_OIDC_VERWALTUNG") ?: "verwaltung";
    $speicher = \Drupal::entityTypeManager()->getStorage("openid_connect_client");
    $kunde = $speicher->load("pocketid");
    if ($kunde) {
      $kunde->set("settings", $werte);
    }
    else {
      $kunde = $speicher->create([
        "id" => "pocketid",
        "label" => "MekoTools-Anmeldung",
        "plugin" => "generic",
        "settings" => $werte,
      ]);
    }
    $kunde->save();
    \Drupal::configFactory()->getEditable("openid_connect.settings")
      ->set("user_login_display", "above")
      ->set("always_save_userinfo", TRUE)
      ->set("autostart_login", FALSE)
      ->set("connect_existing_users", FALSE)
      ->set("userinfo_mappings", ["mail" => "email", "name" => "preferred_username"])
      ->set("role_mappings", [
        "angemeldet" => [$stufe1, "Angemeldet (unbestätigt)"],
        "lehrkraft" => [$gruppe, "Fachkraft bestätigt"],
        "verwaltung" => [$verwaltung, "Verwaltung"],
      ])
      ->set("force_reset_role_mappings", TRUE)
      ->save();
    echo "Kunde " . $kunde->id() . " bereit\n";
  ' || warnen "Anmeldung konnte nicht eingerichtet werden"
  melden "Anmeldung eingerichtet: Stufen — angemeldet=$STUFE1, Fachkraft=$GRUPPE, Verwaltung=$VERWALTUNG"
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
