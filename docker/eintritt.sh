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
DB_HOST="${STUDIO_DB_HOST:-db}"
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
\$settings['trusted_host_patterns'] = ['^$(printf '%s' "$HOST" | sed -e 's/\./\\./g')\$', '^localhost\$'];
// Die Verschlüsselung endet am VPS; der Vermittler setzt die Kopfzeilen.
\$settings['reverse_proxy'] = TRUE;
\$settings['reverse_proxy_addresses'] = ['$PROXY_NETZ'];
PHP
chown www-data:www-data "$SETTINGS" "$MERKE" 2>/dev/null || true
chown -R www-data:www-data "$DATEIEN" "$PRIVAT" "$DATEN/config"
chmod 644 "$SETTINGS"

# --- 3. Einrichten oder aktualisieren ---------------------------------------
if [ ! -f "$MERKE" ]; then
  melden "Ersteinrichtung beginnt (einmalig, dauert ein bis zwei Minuten)"
  # Der Verweis enthält das Passwort. Erzeugt wird es als Hex-Wert, deshalb
  # braucht es keine Sonderbehandlung und steht nicht im Protokoll.
  DB_URL="mysql://${DB_USER}:${DB_PASS}@${DB_HOST}:3306/${DB_NAME}"
  drush site:install standard \
    --db-url="$DB_URL" \
    --account-name="$ADMIN_NAME" \
    --account-mail="$ADMIN_MAIL" \
    --account-pass="$ADMIN_PASS" \
    --site-name="H5P-Studio" \
    --site-mail="$ADMIN_MAIL" \
    -y

  melden "Bausteine einschalten"
  drush pm:enable -y h5p h5peditor locale language openid_connect mekotools_studio

  melden "deutsche Oberfläche"
  if drush language:add de -y && drush config:set system.site default_langcode de -y \
     && drush locale:check -y && drush locale:update -y; then
    melden "Übersetzungen geladen"
  else
    warnen "Übersetzungen konnten nicht geladen werden — die Oberfläche bleibt teilweise englisch"
  fi

  if [ -n "${STUDIO_OIDC_KENNUNG:-}" ] && [ -n "${STUDIO_OIDC_GEHEIM:-}" ]; then
    melden "Anmeldung über Pocket ID"
    drush php:eval '
      $c = \Drupal::configFactory()->getEditable("openid_connect.settings");
      $clients = $c->get("openid_connect.clients") ?: [];
      $clients["pocketid"] = [
        "id" => "pocketid",
        "label" => "MekoTools-Anmeldung",
        "settings" => [
          "client_id" => getenv("STUDIO_OIDC_KENNUNG"),
          "client_secret" => getenv("STUDIO_OIDC_GEHEIM"),
          "issuer" => getenv("STUDIO_OIDC_ISSUER") ?: "https://auth.mekotools.de",
        ],
        "plugin" => "generic",
      ];
      $c->set("openid_connect.clients", $clients)->save();
      echo "OIDC-Mandant gesetzt\n";
    ' || warnen "OIDC-Mandant konnte nicht gesetzt werden"
  else
    warnen "keine OIDC-Kennung gesetzt — es gilt vorerst nur das Notfallkonto"
  fi

  drush cache:rebuild -y || true
  touch "$MERKE"
  chown www-data:www-data "$MERKE"
  melden "Ersteinrichtung abgeschlossen"
else
  melden "Einrichtung vorhanden — Aktualisierungen laufen"
  drush updatedb -y || warnen "Datenbank-Aktualisierung schlug fehl"
  drush cache:rebuild -y || warnen "Zwischenspeicher liess sich nicht leeren"
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
