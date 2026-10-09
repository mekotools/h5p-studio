# MekoTools H5P-Studio

Browser-Werkstatt für H5P-Inhalte: Lehrkräfte erstellen interaktive Übungen,
finden sie in einer gemeinsamen Bibliothek wieder und teilen sie per Link oder
als `.h5p`-Datei. Läuft als eigene Instanz unter `h5p-studio.mekotools.de`
hinter der MekoTools-Anmeldung (Pocket ID).

## Fähigkeiten (Index)

| Fähigkeit | Spec | Wofür |
|---|---|---|
| `anmeldung` | `specs/anmeldung/spec.md` | Zugang über Pocket ID, Konten entstehen bei der ersten Anmeldung |
| `inhalte` | `specs/inhalte/spec.md` | H5P-Inhalte anlegen, bearbeiten, löschen, herunterladen |
| `bibliothek` | `specs/bibliothek/spec.md` | gemeinsame Sammlung: suchen, filtern, abspielen, teilen |
| `betrieb` | `specs/betrieb/spec.md` | Abbild aus der CI, Stapel auf flip, Sicherung, deutsche Oberfläche |

## Außensysteme

- **Pocket ID** (`auth.mekotools.de`) — OIDC-Anbieter, Konten und Passkeys.
- **H5P-Hub** (`api.h5p.org`) — Verzeichnis der H5P-Inhaltstypen; wird beim
  Auswählen eines Inhaltstyps befragt, Inhalte gehen dorthin nicht.
- **GTmetrix/GHCR** — die Abbilder entstehen in der eigenen CI (Forgejo-Läufer
  `flip-01`) und liegen als `ghcr.io/mekotools/h5p-studio`.

## Architektur in einem Satz

Ein Stapel (`web` + `db`) auf flip, `web` ist ein selbst gebautes Drupal-11-Abbild
mit dem amtlichen H5P-Modul und einem eigenen Modul `mekotools_studio`, das die
Inhaltsart, das H5P-Feld, die Bibliotheksansicht und die deutschen Beschriftungen
als Konfiguration mitbringt.

## Konventionen

- Sprache der Dokumentation: Deutsch (dieses Repo hat keine englische Vorgeschichte);
  OpenSpec-Header bleiben tool-nativ (`## Purpose`, `## Requirements`,
  `### Requirement:`, `#### Scenario:`).
- Änderungen am Verhalten: neuer Change unter `openspec/changes/<id>/`, Specs
  werden beim Archivieren in `specs/` nachgezogen.
- Geheimnisse stehen nie im Repo: `.env` auf dem Zielrechner, `.env.beispiel` im Repo
  nennt nur die Namen.
