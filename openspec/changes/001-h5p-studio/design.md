# Design — H5P-Studio

## Ausgangslage (geprüft, nicht angenommen)

| Frage | Befund | Beleg |
|---|---|---|
| Lizenz des OER-Studio-Schnappschusses? | keine Lizenzdatei, `license: null` | GitHub-Schnittstelle, beide Repos, 08.10.2026 |
| Alter? | letzter Commit 17.12.2021 | Commits-Schnittstelle |
| Unterbau? | `drupal/core 8.9.20`, PHP `^7.0.8`, 172 Pakete | `composer.lock` des Projekts |
| Drupal 8 noch versorgt? | nein, seit 17.11.2021 am Lebensende | drupal.org PSA-2021-11-30 |
| Bauweg vorhanden? | nein (kein Dockerfile, kein CI) | Dateibaum des Repos |
| Heutiger Baukasten offen? | nur Einzelteile, teils ohne Lizenz | Organisations-Repos, 08.10.2026 |
| Tragfähiger Boden? | amtliches `h5p` 2.0.0-beta1 (24.09.2025) für Drupal `^10.2 \|\| ^11` | updates.drupal.org |

## Entscheidungen

1. **Drupal 11.4.8 + amtliches H5P-Modul statt Drupal 8.** Derselbe Aufbau wie das
   Original (H5P-Feld an einer Inhaltsart, H5P-Editor als Feld-Widget), aber auf einer
   Kernversion, die Sicherheitskorrekturen bekommt. Das Modul ist eine Beta — das wird
   offen benannt, nicht versteckt.
2. **Eigenes Modul `mekotools_studio` trägt die Konfiguration** (Inhaltsart „H5P-Inhalt",
   Feld `field_h5p`, Bibliotheksansicht, Rollen, deutsche Beschriftungen) als
   `config/install`. Verworfen: Konfiguration von Hand in der Oberfläche (nicht
   nachbaubar), Drupal-Rezepte (in dieser Kernversion noch nicht überall stabil), ein
   Installationsprofil (mehr Maschinerie als Nutzen).
3. **Anmeldung über Pocket ID per `openid_connect`** (3.0.0-alpha9, Drupal `^10.2 || ^11`).
   Kein Vorhängen der Tinyauth-Sperre: das Werkzeug bringt eigenes OIDC mit, Konten und
   Rollen gehören in den Anmeldedienst (Hausregel). Die **Sperre wäre hier auch falsch**:
   Lernende müssen Inhalte ohne Konto öffnen können.
4. **Zwei Behälter** (`web`, `db`) in einem Stapelordner. `web` ist das eigene Abbild,
   `db` ist `mariadb`. Folge für die Kennzeichnungen: `traefik.docker.network=coolify`
   darf **nicht** gesetzt werden — der Behälter hängt an zwei Netzen, der Vermittler
   würde ihn sonst vollständig überspringen (bekannte Falle, im Katalog-Skill belegt).
5. **Bodensatz: `drupal:11.4.8-apache`** (Debian, 207 MB) statt einer Fassung mit
   `php-fpm` + separatem nginx. Abwägung: die Alpine-Variante spart rund 80 MB, kostet
   aber einen zweiten Prozess im Behälter (Supervisor) und damit eine neue Fehlerquelle
   beim Start. Der Großteil des Abbilds ist ohnehin Drupal selbst. Die Fassung ist
   festgenagelt; ein späterer Umbau auf `fpm-alpine` + `nginx:alpine-slim` bleibt offen.
6. **Installation beim ersten Start** (Datenbank steht erst dann), nicht beim Bauen:
   `drush site:install standard` mit Werten aus der Umgebung, danach Module an,
   deutsche Sprache, Übersetzungen. Erfolg wird mit einer Merkdatei quittiert; ohne
   Merkdatei läuft die Einrichtung erneut. Kein „halb eingerichtet, niemand merkt es".
7. **Datenhaltung:** Datenbank in einem eigenen Volume, Dateien (H5P-Inhalte,
   Bibliotheken) in einem eigenen Volume. `rsnapshot` auf flip erfasst `/poolio`, der
   Stapelordner ist damit mitgesichert; das Datenbank-Volume braucht zusätzlich einen
   Abzug im Klartext (siehe tasks).
8. **Datenschutz:** `h5p_send_usage_statistics` wird auf **0** gesetzt (Voreinstellung
   wäre 1 — das schickte Nutzungsdaten an h5p.org). Das Verzeichnis der Inhaltstypen
   kommt weiterhin vom H5P-Hub (Netzverbindung vom Server, kein Inhalt verlässt ihn);
   ohne diesen Abruf gäbe es keine Auswahl an Inhaltstypen. Das wird in der
   Betriebsdokumentation offen benannt.

## Offene Punkte (werden im Lauf belegt, nicht geraten)

- **`composer.lock`:** die erste Fassung löst die Abhängigkeiten im Bau auf (kein
  Schloss im Repo). Nach dem ersten grünen Lauf wird das Schloss aus dem Abbild
  gezogen und eingecheckt — danach baut derselbe Quellstand dasselbe Abbild.
- **H5P-Bibliotheken:** Inhaltstypen werden serverseitig vom H5P-Hub geholt. Ob sich
  die empfohlenen Typen schon beim Start vorladen lassen (statt beim ersten Klick einer
  Lehrkraft), ist zu messen; die Antwort gehört in die Betriebsdokumentation.
- **Deutsche Übersetzung** kommt beim Einrichten aus `localize.drupal.org`. Schlägt das
  fehl, bleibt die Oberfläche englisch — das wird laut protokolliert und gemeldet, nicht
  stillschweigend hingenommen.
- **Größe:** Abbild und Volumes nach dem ersten Ausrollen messen (Platz auf flip).

## Verworfen

- **Den 2021er Schnappschuss betreiben** (Weg 2 der Vorlage): ohne Lizenz kein Recht
  zum Betreiben oder Ändern, und ein fünf Jahre ungepatchtes Drupal 8 ins Netz zu
  stellen, wäre gegenüber Schulen nicht zu verantworten.
- **Nur verlinken** (Weg 3): löst das Problem nicht — die Inhalte lägen bei einem
  US-Anbieter, und der Katalog verspricht für `betrieb: fremd` einen Datenhinweis
  statt eines eigenen Angebots.
- **Moodle als Unterbau:** spielt H5P ab, ist aber ein Lernmanagementsystem — zu groß
  für „H5P-Werkstatt", und die Bedienung einer Lehrkraft wäre eine Zumutung.
- **Eigenbau auf `H5P-Nodejs-library`** (GPL-3.0, aktiv): schlanker, aber Bibliothek,
  Rollen, Suche und Oberfläche müssten selbst gebaut werden. Bleibt Option für später,
  wenn der Drupal-Unterbau zu schwer wird.
