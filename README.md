# MekoTools — H5P-Studio

Eine Werkstatt für H5P-Inhalte im Browser: Lehrkräfte erstellen interaktive
Übungen, finden sie in einer gemeinsamen Bibliothek wieder und teilen sie über
eine Adresse, die ohne Konto funktioniert.

Läuft unter **`studio.mekotools.de`** (Stapel auf flip: der Web-Dienst am Netz
`coolify`, die Datenbank ausschließlich im eigenen internen Netz).

## Aufbau

| Teil | Was es ist |
|---|---|
| Grundlage | amtliches `drupal:11.4.8-apache` (PHP 8.4, Apache, composer) |
| `drupal/h5p` | amtliches H5P-Modul **2.0.0-beta1** (24.09.2025) — Inhaltstypen, Editor, Abspielen |
| `drupal/openid_connect` | Anmeldung über **Pocket ID** (Passkey), **3.0.0-alpha9** |
| `drush` | Einrichtung und Wartung auf der Kommandozeile |
| `studio/mekotools_studio` | **eigener Aufsatz**: Inhaltsart „H5P-Inhalt", Feld, Fach/Schlagworte, Bibliotheksseite, Rolle „Lehrkraft", deutsche Beschriftungen, Datenschutz-Voreinstellung |
| `studio/mekotools_huelle` | **eigenes Gerüst** (Thema) mit der Gestalt von mekotools.de |
| `docker/eintritt.sh` | richtet beim ersten Start selbst ein (Datenbank, Sprache, Übersetzungen) und aktualisiert danach nur noch |

## Die Hülle (MekoTools-Hülle)

Wer das Studio aufruft, soll nicht auf einer fremden Website landen. Deshalb
trägt das Studio die Gestalt von **mekotools.de** (dort läuft MkDocs Material):

- Kopfleiste in der Hausfarbe **`#009485`**, Werkzeugkasten-Zeichen, Hausname über
  dem Werkzeugnamen, immer sichtbarer Rückweg zu mekotools.de.
- Gleiche Inhaltsbreite (61rem), gleiche Fußzeile mit Lizenzhinweis,
  **hell und dunkel nach Systemeinstellung**.
- **Keine fremden Schriftarten und keine Abrufe von außen** (Material lädt Roboto
  von einem fremden Server; hier gilt die Systemschrift).
- Verweise in einem dunkleren Türkis (`#007a6c`), damit sie auf weißem Grund gut
  lesbar sind — die Kopfleiste behält die Hausfarbe.
- **Konto-Bereich in der Kopfleiste:** „Anmelden" führt direkt zu Pocket ID
  (`js/anmeldung.js` reicht Drupals Zwischenseite in einem Zug weiter), nach der
  Anmeldung stehen dort der eigene Name und „Abmelden". Drupals eigene
  Anmeldeseite `/user/login` bleibt der Verwaltung vorbehalten.
- **Bibliothek und Beitragsseiten im Stil des Werkzeugkatalogs:** Jeder Eintrag
  trägt unter dem Titel eine **Beschreibung — was die Lektion enthält**
  (Pflichtfeld `field_beschreibung`; ohne sie ist ein Titel allein nicht zu
  deuten). Auf schmalen Geräten wird die Tafel zur Blockliste, nichts rollt
  seitwärts; die Kopfleiste bricht um. Beitragsseiten sehen aus wie die
  Werkzeugseiten auf mekotools.de: Rückweg, Titel, Beschreibung als Einleitung,
  Angaben, Übung, Adresse zum Weitergeben. Siehe
  `openspec/changes/004-bibliothek-und-beitragsseiten/`.
- **Kein Brotkrümel:** Drupals Brotkrümel zeichnet eine numerierte Liste und
  hieße auf der Startseite bloß „1. Startseite" — auf einer Beitragsseite fehlte
  der Seitenname. Zurück in den Katalog führt der Verweis auf jeder
  Beitragsseite.

Die ganze Umgebung trägt dieselbe Gestalt: **Pocket ID** (Name, Hausfarbe,
Zeichen, Favicon, Mailbild, Kunden) und **Tinyauth** (Titel, Hintergrund) werden
von `ressourcen/pocket-id-einrichten.py` bzw. Zeilen in der `.env` des
Anmelde-Stapels gestellt — siehe Skill `mekotools-auth-stack`.

Gesetzt wird die Hülle als **Aktualisierungsschritt** (`mekotools_studio_update_10005`):
Gerüst, Startseite `/bibliothek` („wer das Studio aufruft, sieht den Katalog"),
Menü und Blockordnung. So hat auch eine frisch aufgebaute Instanz dieselbe
Oberfläche, ohne Handarbeit.

Für die **Verwaltung** bleibt Drupals eigene Oberfläche (Claro) zuständig.

## Warum nicht „OER Studio"?

Der offene Bauplan von Learnful (`learnfullabs/oerstudio-project`) wurde am
08.10.2026 geprüft und scheidet aus: **keine Lizenzdatei** in beiden beteiligten
Repos, letzter Commit 17.12.2021, festgeschrieben auf `drupal/core 8.9.20` —
Drupal 8 ist seit dem 17.11.2021 am Lebensende — und kein Bauweg. Details in
`openspec/changes/001-h5p-studio/design.md`.

Gebaut wird derselbe Aufbau auf gepflegtem Boden: Drupal 11 mit dem amtlichen
H5P-Modul.

## Entwicklung und Auslieferung

Gebaut wird **ausschließlich in der eigenen Fertigung** (Forgejo-Läufer auf flip)
und nie auf dem Zielrechner: `.forgejo/workflows/abbild.yml` baut und schiebt
nach `ghcr.io/mekotools/h5p-studio`. Der Stapel auf flip zieht das Abbild
**festgenagelt** an seinem Verdauungswert.

```bash
# Neuen Stand ausliefern (Digest steht im Protokoll des Fertigungslaufs)
ssh -J n0ne root@192.168.1.20 'cd /poolio/docker/mekotools-studio && \
  docker compose pull web && docker compose up -d'
```

Betrieb, Sicherung und Rückweg: **docs/betrieb.md** (folgt).

## Fassungen

- Drupal 11.4.8 · H5P-Modul 2.0.0-beta1 · openid_connect 3.0.0-alpha9 · Drush 13
- Anleitung für Lehrkräfte: `docs/anleitung.md` · Didaktik: `docs/didaktik.md` ·
  Unterrichtsentwurf: `docs/unterrichtsentwurf.md`
- Im MekoTools-Katalog: <https://mekotools.de/werkzeuge/h5p-studio/> — die
  Katalogbausteine (`tool.yaml` und die drei Dokumente unter `docs/`) liegen in
  diesem Repository
- Entwurf und Stand der Umsetzung: `openspec/changes/001-h5p-studio/`

## Grenzen (ehrlich)

- Das H5P-Modul ist eine **Beta** (2.0.0-beta1). Es funktioniert, ist aber nicht
  „stabil" im Sinne einer Langzeit-Freigabe.
- Der Katalog der Inhaltstypen kommt vom **H5P-Hub** (`api.h5p.org`, Netzverbindung
  des Servers). Ohne ihn gäbe es keine Auswahl an Inhaltstypen; Inhalte selbst
  verlassen den Server an dieser Stelle nicht.
- Es gibt **keine** Kurs-/Sammlungsstruktur und **keine** LTI-Anbindung an ein
  Lernmanagementsystem — beides wären eigene Vorhaben.
- Änderungen werden nicht versioniert: eine Bearbeitung überschreibt den Inhalt.
