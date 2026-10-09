## ADDED Requirements

### Requirement: Gemeinsame Bibliothek mit Suche und Filtern

Alle Inhalte des Hauses stehen in einer Liste: Titel, Inhaltstyp, Fach, Schlagworte,
wer ihn angelegt hat und wann. Ein Suchfeld findet Inhalte über den Titel, Filter
schränken auf Fach und Schlagwort ein. Die Zahl der angezeigten Inhalte wird genannt.

#### Scenario: Suchen
- **Eingaben:** ein Wort aus dem Titel
- **Ergebnis:** Nur passende Inhalte stehen in der Liste; die Anzahl ist angegeben

#### Scenario: Kein Treffer
- **Eingaben:** ein Wort, das in keinem Titel vorkommt
- **Ergebnis:** Ein sichtbarer Hinweis, dass nichts gefunden wurde — nicht eine leere Fläche ohne Erklärung

### Requirement: Abspielen ohne Konto

Wer die Adresse eines Inhalts hat, kann ihn abspielen, ohne sich anzumelden. Das gilt
für den bestimmungsgemäßen Weg (Klassenraum, Lernende auf Tablets) und wird durch keinen
Vorgang im Studio stillschweigend abgeschaltet.

#### Scenario: Aufruf ohne Anmeldung
- **Akteure:** Lernende ohne Konto
- **Ablauf:** Aufruf der Inhaltsadresse
- **Ergebnis:** Der Inhalt spielt; die Bibliothek und die Bearbeitung bleiben verschlossen

#### Scenario: Bearbeiten bleibt verschlossen
- **Ablauf:** Aufruf der Bearbeitungsadresse ohne Anmeldung
- **Ergebnis:** Anmeldung wird verlangt; kein Bearbeitungsformular erscheint

### Requirement: Teilen per Adresse

Zu jedem Inhalt gibt es eine kopierbare Adresse, die auf den Inhalt selbst zeigt
(nicht auf die Bearbeitung). Sie ist im Studio sichtbar und in einem Schritt zu
übernehmen.

#### Scenario: Adresse übernehmen
- **Ablauf:** Inhalt öffnen → Adresse kopieren → in einem neuen, unangemeldeten Browserfenster aufrufen
- **Ergebnis:** Derselbe Inhalt spielt

### Requirement: Herkunft der Inhalte bleibt sichtbar

Zu jedem Inhalt ist erkennbar, wer ihn angelegt hat. Fremde Inhalte dürfen verändert
werden (gemeinsame Sammlung), aber die Herkunft wird dabei nicht umgeschrieben.

#### Scenario: Bearbeiten eines fremden Inhalts
- **Ablauf:** Lehrkraft B öffnet den Inhalt von Lehrkraft A und speichert eine Änderung
- **Ergebnis:** Der Inhalt bleibt A zugeordnet; die Änderung ist gespeichert
