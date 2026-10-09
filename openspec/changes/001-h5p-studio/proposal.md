# Change 001 — H5P-Studio (Erstellen, Sammeln, Abspielen)

**Status:** In Arbeit
**Datum:** 08.10.2026
**Auslöser:** Nutzerauftrag „Baue OER Studio in MekoTools ein" (08.10.2026),
Entscheidung für Weg 1 — eigener Bau auf gepflegtem Boden.

## Why

Der Katalog empfiehlt bisher **Lumi** (Arbeitsplatz-Programm) zum Erstellen und
`h5p-standalone` zum Abspielen. Beides ist ehrlich, aber unvollständig: zum Erstellen
muss etwas installiert werden, und es gibt keinen gemeinsamen Ort, an dem Inhalte
wiederauffindbar liegen. Der offene Bauplan „OER Studio"
(`learnfullabs/oerstudio-project`) scheidet nach Prüfung aus: keine Lizenzdatei in
beiden beteiligten Repos, letzter Commit 17.12.2021, festgeschrieben auf
`drupal/core 8.9.20` — Drupal 8 ist seit 17.11.2021 am Lebensende — und kein Bauweg.
Der heutige Baukasten der Anbieterfirma (LibreStudio, eCampusOntario, K-12 Studio) ist
nicht veröffentlicht.

Gebaut wird deshalb derselbe Aufbau auf gepflegtem Boden: Drupal 11.4.8 mit dem
amtlichen H5P-Modul (2.0.0-beta1, für Drupal `^10.2 || ^11`) und eigener
Konfiguration für Inhaltsart, Bibliothek und deutsche Oberfläche.

## What Changes

Neu ist eine **laufende Studio-Instanz** unter `h5p-studio.mekotools.de`:

- Eine Lehrkraft meldet sich **einmal** mit ihrem Pocket-ID-Passkey an; ein Konto
  entsteht dabei von selbst, ein zweites Kennwort gibt es nicht.
- Sie **legt einen H5P-Inhalt an**: Titel, Fach/Themen, Inhaltstyp aus dem
  H5P-Verzeichnis, dann der bekannte H5P-Editor im Browser. Der Entwurf bleibt
  gespeichert und lässt sich später weiterbearbeiten.
- Sie **findet Inhalte wieder**: eine Bibliotheksseite listet alle Inhalte des
  Hauses mit Suchfeld, Fach- und Schlagwortfilter; jede Zeile führt zum Abspielen
  und zum Herunterladen als `.h5p`-Datei.
- **Teilen** geschieht über die Adresse des Inhalts; Lernende brauchen dafür **kein**
  Konto (das ist die Bedingung dafür, dass das Werkzeug im Unterricht taugt).
- Die Oberfläche ist **deutsch**; die Rechtstexte-Angaben des H5P-Editors
  (Nutzungsrechte je Inhalt) bleiben erhalten.
- Der Betrieb ist reproduzierbar: das Abbild entsteht in der CI, der Stapel auf flip
  zieht es festgenagelt, die Konfiguration liegt als Code im Modul `mekotools_studio`
  (keine Konfiguration von Hand in einer Datenbank, die niemand nachbauen kann).

Nicht in diesem Change (eigene Changes, sobald gebraucht): eigene Sammlungen/Kurse,
LTI-Anbindung an ein Lernmanagementsystem, Versionsverlauf einzelner Inhalte,
Freigabe je Inhalt (privat/geteilt).

## Specs-Delta

- **ADDED** `anmeldung` — Zugang über Pocket ID, Konten bei der ersten Anmeldung.
- **ADDED** `inhalte` — Anlegen, Bearbeiten, Löschen, Herunterladen.
- **ADDED** `bibliothek` — Suchen, Filtern, Abspielen, Teilen.
- **ADDED** `betrieb` — Abbild, Stapel, deutsche Oberfläche, Sicherung.

## Archivierungs-Hinweis

Frisches Repo, erste Fassung: die Live-Specs unter `specs/` entstehen beim
Archivieren aus den ADDED-Deltas dieses Changes (nicht vorab von Hand anlegen —
sonst scheitert das CLI-Archiv an „already exists").
