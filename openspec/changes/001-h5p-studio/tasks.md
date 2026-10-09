# Tasks — Change 001 (H5P-Studio)

## A. Entwurf und Gerüst

- [x] A1 Bestand prüfen (Lizenz, Alter, Unterbau, Bauweg des OER-Studio-Schnappschusses) — belegt im Design
- [x] A2 Tragfähigen Boden bestimmen (Drupal 11.4.8 + amtliches H5P-Modul 2.0.0-beta1)
- [x] A3 Repo `mekotools-h5p-studio` in der Forgejo-Organisation anlegen
- [x] A4 OpenSpec-Change (dieser Entwurf) schreiben
- [ ] A5 Gerüst: `Dockerfile`, `docker-compose.yml`, `.env.beispiel`, CI-Ablauf, `ausliefern.sh`
- [ ] A6 Eigenes Modul `mekotools_studio` (Inhaltsart, Feld, Bibliotheksansicht, Rolle, deutsche Beschriftungen)

## B. Abbild und CI

- [ ] B1 CI-Ablauf baut und schiebt nach `ghcr.io/mekotools/h5p-studio` (Aufkleber `org.opencontainers.image.source` auf den Spiegel, sonst wird das Paket nicht öffentlich)
- [ ] B2 Erster grüner Lauf; Paket anonym ziehbar nachweisen (`scripts/abbild-anonym-pruefen.py`)
- [ ] B3 `composer.lock` aus dem gebauten Abbild ziehen und einchecken; Bau auf `composer install` umstellen
- [ ] B4 Spiegel-Repo `github.com/mekotools/h5p-studio` anlegen (Quelle ist Forgejo)

## C. Auslieferung auf flip

- [ ] C1 Stapelordner `/poolio/docker/mekotools-h5p-studio/` mit `docker-compose.yml` und `.env` (Rechte 600)
- [ ] C2 Datenbank-Volume und Datei-Volume anlegen; Verzeichnisse den richtigen Benutzern geben
- [ ] C3 Erster Start: Einrichtung protokolliert lesen, Merkdatei prüfen, Wiederholung belegen
- [ ] C4 Deutsche Oberfläche belegen (`drush language:info`, eine Seite mit deutschen Beschriftungen)
- [ ] C5 `h5p_send_usage_statistics` steht auf 0 (Gegenprobe: Einstellungsseite des H5P-Moduls)

## D. Anmeldung

- [ ] D1 OIDC-Anwendung in Pocket ID anlegen (Rückleitung `https://h5p-studio.mekotools.de/openid-connect/generic`), Geheimnis in die `.env`
- [ ] D2 Anmeldung in Drupal auf den Anbieter stellen, lokale Anmeldung für das Notfallkonto erhalten
- [ ] D3 Kette belegen: Anmeldeseite → Pocket ID → Rückleitung → Konto in Drupal; Rolle „Lehrkraft" wird vergeben
- [ ] D4 Notfall-Zugang auf dem Zielrechner dokumentieren (Pfad, Rechte 600; Kennwort nie im Chat)

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

- [ ] G1 Von außen: Startseite, Bibliothek und ein Inhalt je mit Statuscode **und** Titel
- [ ] G2 Sicherung rückwärts geprobt (Volume-Abzug, Wiederherstellung beschrieben)
- [ ] G3 Offene Punkte aus dem Design beantwortet oder ausdrücklich als offen gemeldet
