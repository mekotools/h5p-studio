# Change 004 — Bibliothek als Katalog, Beitragsseiten als Werkzeugseiten

## Warum

Die Bibliothek war eine Tafel ohne Gestalt: fünf Spalten, ein Titel, sonst nichts.
Zu einem Eintrag stand nur der Name — nicht, was die Lektion enthält. Wer sucht,
musste jeden Eintrag öffnen, um zu wissen, ob er passt. Auf dem Telefon rollte die
Tafel außerdem seitwärts: gemessen am 09.10.2026 bei 390 px Fensterbreite war die
Tafel **1010 px** breit in einem 364-px-Bereich. Der Auftrag (Wortlaut):

> „Die einzelnen H5P-Beiträge sollen auch im gleichen Style angezeigt werden wie die
> Werkzeuge auf der mekotools Seite."

und

> „Man muss zu dem Titel auch eine Beschreibung haben, was die Lektion beinhaltet."

Der Werkzeugkatalog auf mekotools.de macht genau das vor: Werkzeugname, darunter
klein die Beschreibung, daneben die Angaben, und auf schmalen Geräten wird aus der
Tafel eine Blockliste — mit der Begründung, dass auf dem Telefon die Scrollstrecke
das knappste Gut ist (so im Katalog-Repo vermerkt).

## Was sich ändert

1. **Beschreibung als Pflichtfeld.** Neue Inhaltsart-Felder: `field_beschreibung`
   (mehrzeiliger Text, **Pflicht**), im Formular über der Übung, mit dem Hinweis
   „Was enthält die Lektion? Ein bis zwei Sätze."
2. **Beschreibungen für die 30 vorhandenen Beispiele** — erhoben aus dem Lehrtext
   der Pakete selbst (`werkzeuge/beschreibungen-einspielen.php`), nicht ausgedacht.
3. **Bibliothek im Stil des Katalogs:** Beschreibung klein unter dem Titel, Angaben
   daneben, „Abspielen" als Knopf am Zeilenende. Unter 46em wird die Tafel zur
   Blockliste mit Beschriftungen („Fach:", „Schlagworte:", „Geändert:") — nichts
   rollt mehr seitwärts.
4. **Beitragsseite im Stil einer Werkzeugseite:** Rückweg in die Bibliothek, Titel,
   die Beschreibung als Einleitung, die Angaben, dann die Übung; die Adresse zum
   Weitergeben als eigener Kasten.
5. **Kopfleiste bricht um**, statt über den Rand zu laufen (bei 390 px gemessen:
   9 px Überlauf, weil Rückweg und Konto nebeneinander nicht passten).

## Nebenbefund, der mitbehoben wird

Die Grundfassung des Moduls trug die Felder der Beitragsseite in den
Ansichtsmodus **`default`** ein. Die Seite eines Inhalts wird aber im Modus
**`full`** gezeichnet — Felder, die nur in `default` stehen, erscheinen nie. Genau
das war der Fall. Die Eintragung wandert nach `full`; `field_h5p` bleibt bewusst
heraus, weil der H5P-Baustein die Übung selbst beiträgt und sie sonst doppelt
stünde.

## Grenzen

- Die Beschreibungen sind von der KI aus den Paketinhalten geschrieben. Herkunft
  und Verfahren stehen in der Quelldatei und werden beim Einspielen mitgemeldet.
  Wer eine bessere Formulierung hat, ändert sie im Studio — das Einspielwerkzeug
  überschreibt vorhandene Texte nur mit `--ueberschreiben`.
- Die Gestalt wird nicht maschinell bewacht (Entscheidung vom 09.10.2026): Die
  Betreiberin/der Betreiber sagt Bescheid, wenn etwas zerbricht.
