# Tasks — Change 001 (H5P-Studio)

Stand 09.10.2026: Die Instanz **läuft** unter `https://studio.mekotools.de`
(Abbild `ghcr.io/mekotools/h5p-studio`, Stapel `/poolio/docker/mekotools-studio`).
Der Dienst hieß bis zum 09.10.2026 `h5p-studio.mekotools.de`; beim Umbenennen
mussten die Datenträger mitwandern, weil ihr Name vom Stapelordner kommt.
Offen sind Anmeldung (Pocket ID), Dokumente, Katalogeintrag und der Klickweg im Browser.

## A. Entwurf und Gerüst

- [x] A1 Bestand prüfen (Lizenz, Alter, Unterbau, Bauweg des OER-Studio-Schnappschusses) — belegt im Design
- [x] A2 Tragfähigen Boden bestimmen (Drupal 11.4.8 + amtliches H5P-Modul 2.0.0-beta1)
- [x] A3 Repo `mekotools-h5p-studio` in der Forgejo-Organisation anlegen
- [x] A4 OpenSpec-Change (dieser Entwurf) schreiben
- [x] A5 Gerüst: `Dockerfile`, `docker-compose.yml`, `.env.beispiel`, CI-Ablauf, `ausliefern.sh`
- [x] A6 Eigenes Modul `mekotools_studio` (Inhaltsart, Feld, Bibliotheksseite, Rolle, deutsche Beschriftungen)
- [ ] A7 Nachziehen: Feld „Kurzbeschreibung" (body) wurde nicht angelegt — Bedingung im Einrichtungsteil greift nicht; entweder anders prüfen oder auf H5P-Metadaten verzichten

## B. Abbild und CI

- [x] B1 CI-Ablauf baut und schiebt nach `ghcr.io/mekotools/h5p-studio`
- [x] B2 Erster grüner Lauf; Paket anonym ziehbar nachgewiesen (`HTTP 200`, `scripts/abbild-anonym-pruefen.py`)
- [ ] B3 `composer.lock` aus dem gebauten Abbild ziehen und einchecken; Bau auf `composer install` umstellen (Schloss liegt bereits lokal vor, 94 Pakete)
- [x] B4 Spiegel-Repo `github.com/mekotools/h5p-studio` angelegt; Erstanlage aus dem Spiegel ließ das Paket öffentlich werden (vorher Forgejo-Erstanlage ⇒ privat, nicht umstellbar)

## C. Auslieferung auf flip

- [x] C1 Stapelordner `/poolio/docker/mekotools-h5p-studio/` mit `docker-compose.yml` und `.env` (Rechte 600)
- [x] C2 Volumes angelegt; Rechte vergibt das Eintrittsskript
- [x] C3 Erster Start protokolliert gelesen; Merkdatei vorhanden; erneuter Start läuft ohne Neueinrichtung
- [x] C4 Deutsche Oberfläche belegt (`system.site:default_langcode = de`, Seitentitel „Bibliothek", „Anmelden")
- [x] C5 `h5p_send_usage_statistics` steht auf 0 (Gegenprobe über `drush config:get`)

## D. Anmeldung

- [ ] D1 OIDC-Anwendung in Pocket ID anlegen — **Rückleitung ist `https://h5p-studio.mekotools.de/openid-connect/pocketid`** (der Pfad ist die Kennung der Mandanten-Entität, nicht der Name des Bausteins); Geheimnis in die `.env`
- [ ] D2 Anmeldung in Drupal auf den Anbieter stellen (das Eintrittsskript legt den Mandanten an, sobald `STUDIO_OIDC_KENNUNG`/`STUDIO_OIDC_GEHEIM` in der `.env` stehen); lokale Anmeldung für das Notfallkonto bleibt
- [ ] D3 Kette belegen: Anmeldeseite → Pocket ID → Rückleitung → Konto in Drupal; Rolle „Lehrkraft" wird vergeben
- [ ] D4 Notfall-Zugang dokumentieren (Pfad, Rechte 600; Kennwort nie im Chat)

## E. Funktionsnachweis (im Browser, von außen)

- [ ] E1 Ein H5P-Inhalt entsteht (Inhaltstyp wählen, speichern), Beleg: Adresse + Bildschirmabzug-Spur
- [ ] E2 Inhalt öffnet ohne Anmeldung (externer Aufruf, zweiter Browser-Zustand ohne Keks)
- [ ] E3 Bibliothek: Suche und Filter greifen; zweiter Inhalt taucht auf
- [ ] E4 Herunterladen als `.h5p` liefert eine Datei, die mit `h5p-standalone` abspielbar ist
- [ ] E5 Bearbeiten eines bestehenden Inhalts speichert die Änderung sichtbar

## F. Katalog und Doku

- [ ] F1 `tool.yaml` (Pflichtfelder, `laufzeit: server`, `zugang: {art: konto, hinweis: …}`, `stufe`, `schlagworte`)
- [ ] F2 `docs/anleitung.md`, `docs/didaktik.md`, `docs/unterrichtsentwurf.md` (laienverständlich, Grenzen benannt)
- [ ] F3 `tool.yaml` gegen `schema/tool.schema.json` prüfen (`check-jsonschema`)
- [ ] F4 Katalog bauen (`bauen.sh`) und ausliefern; Werkzeugzahl in der Schlusszeile prüfen
- [ ] F5 Betriebsdokumentation `docs/betrieb.md` (Stapel, Sicherung, Rückweg, Hub-Abhängigkeit, Datenschutz)

## G. Abnahme

- [x] G1 Von außen: Startseite `200`, Bibliothek `200` (Titel „Bibliothek | H5P-Studio"), Anmeldeseite `200` („Anmelden | H5P-Studio"), `/studio` leitet mit `302` auf die Bibliothek
- [ ] G2 Sicherung rückwärts geprobt (Volume-Abzug, Wiederherstellung beschrieben)
- [ ] G3 Offene Punkte aus dem Design beantwortet oder ausdrücklich als offen gemeldet

## Unterwegs gelernt (in Skill `mekotools-werkzeug-ausliefern` übertragen)

1. Ein Behälter mit **scheiternder Gesundheitsprobe** wird vom Verwalter gar nicht geführt → eigener 404. Ursache hier: Drupals Adresskontrolle lehnt `Host: 127.0.0.1` mit 400 ab.
2. Der Dienstname **`db` ist im Netz `coolify` mehrfach belegt** → eindeutigen Behälternamen ansprechen.
3. Netzordnung: Web an `coolify` **und** eigenes internes Netz, Datenbank **nur** intern; bei zwei Netzen `traefik.docker.network` ausdrücklich setzen.
4. **`h5p/h5p-core` auf 1.27.0 festnageln** — 1.28.0 bricht `drupal/h5p` 2.0.0-beta1.
5. **Erstanlage** des GHCR-Pakets muss aus dem Spiegel-Arbeitsablauf kommen; ein Forgejo-Erstanlage-Paket bleibt privat.
