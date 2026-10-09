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
- [x] 5.2 Fertigungslauf 27 (`1c880d2`) grün; ausgeliefert als
      `sha256:d4e2a64aacf08f6f3d4c8d6f3c7a624a16f1304f1806cfbb7de5dcdeff6b4d5e`;
      am lebenden Dienst gegengeprüft: 25 Zeilen mit Beschreibung auf Seite 1
      (30 gesamt), Beitragsseite mit Beschreibung und Übung, Überlauf 0 px bei 390 px
- [x] 5.3 README um Beschreibung, Tafel und Brotkrümel ergänzt

## 6. Navigationsbereich (Nachtrag, ebenfalls 09.10.2026)

Gemeldet: „Der gesamte Navigationsbereich sieht auf mobile immer noch
unterirdisch aus." Nachgemessen — betroffen war auch der Schreibtisch.

- [x] 6.1 Ursache gefunden: Die Regeln zielten auf `ul.menu`, der Menüblock
      liefert ein `<ul>` **ohne Klasse** — sie griffen nie. Das Hauptmenü stand
      als senkrechte Aufzählungsliste mit weißen Punkten und großem Einzug,
      am Telefon 144 px hoch statt 44 px.
- [x] 6.2 Selektor ohne Klassenbindung; der Drupal-Kasten um den Block darf
      schrumpfen (`.mt-navi .mt-huelle > *`)
- [x] 6.3 Kopfleiste mobil auf einer Zeile: „Zurück zu MekoTools" wird zu
      „← MekoTools" (`.mt-lang` ausgeblendet; Marke 92 px + Rückweg 101 px +
      „Anmelden" 93 px passen bei 390 px)
- [x] 6.4 Leiste mobil eine Reihe: Abstände und Schriftgrad so gewählt, dass
      alle drei Einträge ganz sichtbar sind (vorher 393 px, dritter angeschnitten;
      jetzt 364 px von 364 px). Seitliches Rollen bleibt als Netz stehen.
- [x] 6.5 Gegenmessung: Kopf 46 px + Leiste 44 px = **90 px** (vorher 216 px),
      Aufzählungszeichen `none`, Überlauf 0 px, alle Einträge vollständig
- [x] 6.6 Ausgeliefert als
      `sha256:66ab885a1be26ad85fd69f32658669fd899561339a1d6021fa32bb9485c3ed81`
      (Lauf 28, Commit `e9bb3c8`)

## 7. Nebenbefund

- [x] 7.1 Kein Inhaltsfehler: Der Titel „Urheberrechte" gehört so — das Paket
      heißt tatsächlich so. Die frühere Vermutung (falsch gesetzter Titel) war
      falsch; geprüft am Paket `hub-1291395936003439235.h5p`.
