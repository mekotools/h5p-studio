# Entwurf — Change 003: MekoTools-Hülle

## 1. Grundlage des Gerüsts

Drei Wege standen zur Wahl:

- **Olivero (Kern-Standard) umfärben.** Billig, aber Olivero bringt seine eigenen
  Kopf- und Fußbereiche, sein eigenes Menüverhalten und seine eigenen
  Begrüßungstexte mit. Man kämpft dauerhaft gegen fremdes CSS und erreicht
  „quasi identisch" nie ganz.
- **Eigenes Gerüst auf `stark`.** `stark` ist das absichtlich nackte Gerüst des
  Kerns: saubere Vorlagen, fast kein CSS. Damit gehört jede Zeile Gestaltung uns.
- **Eigenes Gerüst ohne Grundlage.** Maximale Freiheit, aber man muss alle
  Vorlagen selbst mitbringen — für ein Studio mit Bibliothek, Editor, Formularen
  und Verwaltungsseiten unnötig viel.

**Gewählt: eigenes Gerüst `mekotools_huelle` auf der Grundlage `stark`.** Wir
schreiben nur die Vorlagen, die die Hülle ausmachen (Seite, Kopf, Fuß), und ein
einziges Blatt Gestaltung. Alles Übrige (Formulare, Tabellen, Meldungen) erbt
sauberes, schlichtes HTML und wird von unserem Blatt mitgestaltet.

Für die **Verwaltung** bleibt **Claro** zuständig — dort arbeiten wir, dort ist
Drupals eigene Oberfläche richtig.

## 2. Gestalt (aus mekotools.de abgelesen)

Die Hauptseite läuft auf MkDocs Material. Die tragenden Werte sind aus ihrer
Palettendatei abgelesen, nicht geschätzt:

| Gestaltungsmarke | Wert | Vorkommen |
|---|---|---|
| Hausfarbe (Kopfleiste) | `#009485` | `--md-primary-fg-color` |
| heller Ton | `#26a699` | `--md-primary-fg-color--light` |
| dunkler Ton | `#007a6c` | `--md-primary-fg-color--dark` |
| helle Darstellung | weißer Grund, `rgba(0,0,0,.87)` Text | Material „default" |
| dunkle Darstellung | `hsl(200, 15%, 14%)` Grund | Material „slate" |
| Inhaltsbreite | `61rem` (≈ 976 px) | Material `.md-grid` |
| Grundschrift | Systemschrift | bewusst, siehe unten |

**Keine fremden Schriftarten.** Material lädt Roboto von einem fremden Server.
Das Haus lädt nichts von außen: es gilt die Systemschrift (auf den Zielgeräten
ohnehin Segoe UI, San Francisco bzw. Roboto). Das ist eine bewusste Abweichung —
optisch kaum zu sehen, datenschutzfachlich der richtige Weg.

**Hell und dunkel** richten sich nach der Systemeinstellung
(`prefers-color-scheme`), genau wie auf mekotools.de. Kein Umschalter nötig.

## 3. Einrichtung als Code, nicht als Handarbeit

Startseite, Gerüst, Menü und Fußzeile werden **nicht** von Hand in der Datenbank
gesetzt, sondern stehen als Aktualisierungsschritt im Modul
(`mekotools_studio.install` → `mekotools_studio_update_10005`). Grund: die Instanz
muss jederzeit nachbaubar sein, und eine neu aufgesetzte Umgebung soll dieselbe
Hülle haben, ohne dass jemand daran denken muss.

Gesetzt wird:

- `system.theme.default` → `mekotools_huelle`
- `system.site.page.front` → `/bibliothek` (der Katalog ist die Startseite)
- `system.site.name` → `H5P-Studio` (die Kopfleiste zeigt „MekoTools" als Haus und
  „H5P-Studio" als Werkzeug; so bleibt die Startseiten-Überschrift kurz)
- Menü `main`: „Bibliothek" (statt „Startseite"), „MekoTools" (Rückweg),
  „Werkzeugkatalog" (mekotools.de/katalog), „Zugang" (je nach Entscheidung, siehe 4)

## 4. Der Zugangsantrag — offene Entscheidung

Der Auftrag verlangt, dass der Antrag **in MekoTools** gestellt wird, weil er auch
für Shadowbroker und weitere Werkzeuge gilt. Heute steht er als Drupal-Formular
unter `studio.mekotools.de/zugang`. Drei Wege:

- **A — Antragsformular auf mekotools.de, Auswertung wie bisher.** Die Hauptseite
  ist statisch (MkDocs); ein Formular braucht ein Gegenüber. Möglich über eine
  kleine Annahmestelle (eigener Dienst am Haus), die die Anträge in dieselbe
  Tabelle schreibt, die das Studio schon auswertet. Sauber, aber eigene Arbeit und
  ein neuer Dienst.
- **B — Antrag per Nextcloud-Formular.** Das Haus hat Nextcloud; Formulare sind
  dort vorhanden. Die Anträge liegen dann aber nicht in der Auswertung des
  Studios, und die Verwaltung muss zwei Orte kennen.
- **C — Empfohlen: die Studio-Seite `/zugang` wird zu einer reinen Verweis-Seite
  auf einen MekoTools-Antrag**, und der MekoTools-Antrag ist die Freigabe über die
  MekoTools-Anmeldung (Pocket ID), die ohnehin für alle Werkzeuge gilt. Die
  tatächliche Auswertungslogik (Freigabe, Einladung per E-Mail) bleibt im Studio,
  weil sie dort schon gebaut und gemessen ist — nur der *Einstieg* wandert nach
  MekoTools.

**Empfehlung: C.** Sie erfüllt den Auftrag („Antrag erfolgt in MekoTools"), nutzt
die vorhandene, geprüfte Freigabe-Mechanik und verlangt keinen neuen Dienst. Für
die Umsetzung braucht es eine Seite auf mekotools.de („Zugang") und auf der
Studio-Seite nur noch einen Verweis — beides ist klein.

**Bis zur Entscheidung** bleibt das vorhandene Formular erreichbar; die Hülle
weist es aber nicht mehr in der Kopfleiste aus. So ist das Studio schon jetzt
„seamless", ohne dass eine Freigabe-Wege verschwindet.

## 5. Was die Hülle nicht tut

- Sie versteckt den Editor nicht: Anlegen braucht weiterhin ein Konto (Stufe 1).
- Sie ändert keine Rechte. Wer nicht angemeldet ist, sieht den Katalog und kann
  Beispiele abspielen — mehr nicht.
- Sie erfindet keine neuen Menüpunkte, die es nicht gibt.
