# Change 003 — MekoTools-Hülle für das Studio

## Warum

Wer das Studio aufruft, verlässt MekoTools: anderes Aussehen, Drupal-Begrüßungstexte
(„Welcome!", „Sie haben noch keinen Inhalt für die Startseite erstellt.", „Komm wegen
des Codes, bleib wegen der Community") und ein eigenes Anmeldeformular. Für
Lehrkräfte sieht das aus wie eine fremde Website, nicht wie ein Werkzeug des Hauses.

Der Auftrag (Wortlaut der Nutzerin/des Nutzers):

> „Die H5P-Einbindung muss ‚seamless' sein. Im Moment verlässt man MekoTools und
> kommt auf eine andere Webseite. Drupal muss so gestylt werden, dass es quasi
> identisch aussieht wie der Rest von MekoTools. Ein Aufruf von H5P-Studio öffnet
> automatisch unseren öffentlichen Katalog. Der Editor selber ist dann per Link
> erreichbar. Der Antrag auf Zugang zu MekoTools erfolgt nicht in Drupal / H5P-Studio
> sondern in MekoTools, da er ja auch für andere Bereiche wie Shadowbroker gilt."

## Was sich ändert

1. **Hülle statt Fremdgerüst.** Das Studio bekommt ein eigenes Gerüst, das die
   Gestalt von mekotools.de aufnimmt: türkise Kopfleiste (`#009485`, die Farbe des
   MkDocs-Material-Gerüsts der Hauptseite), Werkzeugkasten-Zeichen, gleiche Breite,
   gleiche Fußzeile, helle und dunkle Darstellung nach Systemeinstellung.
2. **Keine Drupal-Standardtexte.** Startseite ist die **Bibliothek**; die
   Drupal-Begrüßungsseite verschwindet damit. Beschriftungen, Seitentitel und
   Fußzeile kommen aus dem Haus, nicht aus dem Kern.
3. **Der Aufruf öffnet den Katalog.** `studio.mekotools.de` und `/studio` landen in
   der Bibliothek — dem öffentlichen Katalog. Der Editor bleibt per Link erreichbar
   („Neuen Inhalt anlegen").
4. **Rückweg bleibt sichtbar.** Die Kopfleiste trägt den Weg zurück zu
   `mekotools.de` und zum Werkzeugkatalog; man ist nie „gefangen".
5. **Der Zugangsantrag gehört nach MekoTools**, nicht in das Studio (offene
   Entscheidung, siehe `design.md`, Abschnitt 4).

## Was sich nicht ändert

- Keine Föderation, kein zusätzlicher Identitätsanbieter. Pocket ID bleibt der
  einzige Zugang.
- Keine Änderung an Inhalten, Rechten oder Stufen — 30 Beispiele und die
  Stufenleiter bleiben, wie sie sind.
- Kein fremder Abruf beim Seitenaufbau (keine Schriftarten oder Werbedienste von
  außen): das Haus liefert alles selbst aus.

## Auswirkung

- Betroffen: `studio/mekotools_studio_themes/mekotools_huelle` (neu), das Modul
  `mekotools_studio` (Einrichtung: Startseite, Gerüst, Menü, Fußzeile), `Dockerfile`.
- Nicht betroffen: H5P-Kern, Aufnahme, Bibliothekslogik.
- Prüfbar: Startseite liefert den Katalog, keine Drupal-Begrüßung; Kopfleiste trägt
  die Hausfarbe; Fußzeile ohne „Powered by Drupal"; Seiten funktionieren in hell
  und dunkel; alles ohne Anmeldung erreichbar, Editor nur mit Konto.
