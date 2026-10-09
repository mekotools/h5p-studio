# Aufgaben — Change 004: Bibliothek und Beitragsseiten

## 1. Beschreibung (Auftrag: „was die Lektion beinhaltet")

- [x] 1.1 Feld `field_beschreibung` angelegt (mehrzeilig, **Pflicht**) — in
      `mekotools_studio_install()` und als Aktualisierungsschritt `10006`
- [x] 1.2 Statt Drupals `body`-Feld, das dieser Aufbau nicht mitbringt und das nie
      gefüllt war
- [x] 1.3 Formular: Feld über der Übung, Hinweis „Was enthält die Lektion?"
- [x] 1.4 Beschreibungen der 30 Beispiele aus dem Lehrtext der Pakete erhoben
      (`lektionen-auszug.py` → `lektionen-auszug.json`, 30 Pakete, kein Paket ohne Text)
- [x] 1.5 Texte geschrieben und eingespielt (`werkzeuge/beschreibungen-einspielen.php`,
      `werkzeuge/beschreibungen.json`) — 30 von 30 gesetzt, Längen 81–163 Zeichen
- [x] 1.6 Herkunft der Texte in der Quelldatei vermerkt und beim Einspielen gemeldet

## 2. Bibliothek im Stil des Werkzeugkatalogs

- [x] 2.1 Gestaltungsregeln des Katalogs abgelesen (`docs/stylesheets/extra.css`:
      Tafel, Beschreibung klein unter dem Namen, Blockliste unter 45.9em)
- [x] 2.2 Erzeugung auf die Katalog-Klassen umgestellt (`mt-tafel`,
      `mt-tafel-zusatz`) — eigene Namen, weil `.mt-werkzeug` in der Kopfleiste belegt ist
- [x] 2.3 Beschreibung klein unter dem Titel (`mt-tafel-zusatz`)
- [x] 2.4 „Abspielen" als Knopf statt als nackter Verweis
- [x] 2.5 Fremde Tafeln behalten das seitliche Rollen; die Bibliothek nicht
      (`table:not(.mt-tafel)`)

## 3. Beitragsseite (einzelner Beitrag)

- [x] 3.1 `node--h5p-inhalt.html.twig`: Rückweg, Beschreibung als Einleitung,
      Angaben (Fach, Schlagworte), Übung, Kasten „Adresse zum Weitergeben"
- [x] 3.2 Anzeige-Modus berichtigt: Einträge von `default` nach `full`
      (Felder in `default` erschienen nie — Nebenbefund, siehe proposal.md)
- [x] 3.3 `field_h5p` bleibt aus der Anzeige heraus (sonst stünde die Übung doppelt)
- [x] 3.4 Kasten „Adresse zum Weitergeben" gestaltet; Streichung des Inline-Stils im Modul

## 4. Mobiltauglichkeit

- [x] 4.1 Gemessen bei 390 × 844: Überlauf 9 px (Kopfleiste) und Tafel 1010 px breit
      in 364 px — beides behoben
- [x] 4.2 Blockliste unter 45.9em (Titel und Abspielen in einer Zeile, Beschreibung
      darunter, Angaben mit Beschriftung)
- [x] 4.3 Kopfleiste bricht um (`flex-wrap`), statt über den Rand zu laufen
- [x] 4.4 Gegenmessung nach der Auslieferung: Überlauf 0 px auf `/bibliothek`,
      `/inhalt/30` und der Startseite (390 px) — vorher 9 px; Tafel wird zur
      Blockliste (Tabellenköpfe ausgeblendet)

## 5. Hausordnung

- [x] 5.1 Dieser Change (nachgezogen — der Eingriff erfolgte vor der Niederschrift)
- [ ] 5.2 Fertigungslauf grün, Auslieferung, Digest notieren
- [x] 5.3 README um Beschreibung, Tafel und Brotkrümel ergänzt
