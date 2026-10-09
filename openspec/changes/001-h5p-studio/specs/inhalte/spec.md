## ADDED Requirements

### Requirement: H5P-Inhalt anlegen

Eine angemeldete Lehrkraft legt einen neuen Inhalt an: Titel, Fach und Schlagworte,
dann Auswahl eines Inhaltstyps aus dem Verzeichnis des H5P-Hubs und Ausfüllen im
H5P-Editor. Das Speichern legt den Inhalt dauerhaft ab; ein Inhalt ohne Inhaltstyp
wird nicht gespeichert.

#### Scenario: Neuer Inhalt
- **Eingaben:** Titel, Fach, Schlagwort, Inhaltstyp, ausgefüllter Editor
- **Ergebnis:** Der Inhalt ist gespeichert, hat eine eigene Adresse und erscheint in der Bibliothek

#### Scenario: Speichern ohne Inhaltstyp
- **Eingaben:** Titel, aber kein Inhaltstyp gewählt
- **Ergebnis:** Kein Speichern; eine sichtbare Meldung nennt die fehlende Angabe

### Requirement: Vorhandenen Inhalt weiterbearbeiten

Wer angemeldet ist, öffnet einen bestehenden Inhalt erneut im Editor, ändert ihn und
speichert. Die Änderung ist danach sofort abspielbar; vorherige Fassungen werden nicht
stumm überschrieben, ohne dass die Änderung sichtbar wird.

#### Scenario: Änderung speichern
- **Ablauf:** Inhalt öffnen → Text im Editor ändern → speichern → Inhalt abspielen
- **Ergebnis:** Die geänderte Fassung wird abgespielt

### Requirement: Inhalt als Datei herunterladen

Jeder Inhalt lässt sich als `.h5p`-Datei herunterladen. Die Datei ist eigenständig
abspielbar (in einem H5P-Abspieler oder in einer anderen H5P-fähigen Umgebung).

#### Scenario: Download
- **Ablauf:** Inhalt öffnen → Herunterladen
- **Ergebnis:** Eine `.h5p`-Datei wird geliefert; sie lässt sich in einem unabhängigen Abspieler öffnen

### Requirement: Nutzungsrechte werden erfragt und angezeigt

Der H5P-Editor erhebt Angaben zu Urheber und Lizenz je Inhalt. Diese Angaben werden
beim Abspielen angezeigt, statt verworfen zu werden.

#### Scenario: Lizenzangabe erscheint beim Abspielen
- **Eingaben:** beim Anlegen Urheber und Lizenz eingetragen
- **Ergebnis:** Beim Abspielen ist der Hinweis sichtbar
