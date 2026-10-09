## ADDED Requirements

### Requirement: Abbild aus der eigenen CI, festgenagelt

Das laufende Abbild entsteht in der eigenen Fertigung (Forgejo-Läufer auf flip) und
liegt in der Registry. Auf dem Zielrechner wird kein Quelltext gebaut. Der Stapel zieht
das Abbild an einem Verdauungswert festgenagelt, nicht über eine bewegliche Marke.

#### Scenario: Neuer Stand wird ausgeliefert
- **Ablauf:** Push auf den Hauptzweig → grüner Fertigungslauf → Verdauungswert im Stapel nachziehen → Behälter neu erzeugen
- **Ergebnis:** Die Adresse zeigt den neuen Stand; das Protokoll der Fertigung ist grün

#### Scenario: Rückweg
- **Ablauf:** Der vorherige Verdauungswert wird in den Stapel geschrieben und der Behälter neu erzeugt
- **Ergebnis:** Die vorherige Fassung läuft binnen Sekunden wieder

### Requirement: Ersteinrichtung ohne Handarbeit in der Oberfläche

Der erste Start richtet die Anwendung selbst ein: Datenbank vorbereitet, Anwendung
installiert, benötigte Bausteine eingeschaltet, Sprache gesetzt. Der Erfolg wird mit
einer Merkdatei quittiert. Ein abgebrochener Lauf wird beim nächsten Start wiederholt —
es entsteht kein halb eingerichteter Zustand, der als fertig erscheint.

#### Scenario: Kalter Start
- **Ablauf:** Stapel mit leerer Datenbank starten
- **Ergebnis:** Nach dem Hochfahren antwortet die Adresse mit der Anmeldung; die Merkdatei ist vorhanden

#### Scenario: Wiederholter Start
- **Ablauf:** Behälter neu erzeugen
- **Ergebnis:** Die Einrichtung läuft nicht erneut und ändert den Bestand nicht

### Requirement: Deutsche Oberfläche

Beschriftungen, Meldungen und Menüs stehen auf Deutsch. Fehlt eine Übersetzung, ist die
Stelle auf Englisch sichtbar — sie wird nicht als deutsch ausgegeben und nicht erfunden.

#### Scenario: Oberfläche einer Lehrkraft
- **Ergebnis:** Anmeldung, Bibliothek und Editor tragen deutsche Beschriftungen

#### Scenario: Übersetzung nicht verfügbar
- **Ablauf:** Der Abruf der Übersetzung scheitert beim Einrichten
- **Ergebnis:** Der Vorgang wird laut protokolliert und gemeldet; die Instanz bleibt benutzbar

### Requirement: Keine Nutzungsdaten an Dritte

Das Studio sendet keine Nutzungsdaten an h5p.org. Die Voreinstellung, die das täte,
wird beim Einrichten abgeschaltet und durch eine Gegenprobe belegt. Der Abruf des
Inhaltstypen-Verzeichnisses beim H5P-Hub bleibt bestehen; er wird in der
Betriebsdokumentation offen benannt (Netzverbindung des Servers, kein Inhalt verlässt ihn).

#### Scenario: Gegenprobe der Einstellung
- **Ablauf:** Einstellungen des H5P-Bausteins ansehen
- **Ergebnis:** „Nutzungsdaten senden" ist aus

#### Scenario: Erstellen ohne Hub
- **Ablauf:** Der Hub ist nicht erreichbar
- **Ergebnis:** Eine sichtbare Meldung nennt den Grund; vorhandene Inhalte bleiben abspielbar

### Requirement: Sicherung und Wiederherstellung

Datenbank und Dateien liegen in eigenen Datenträgern. Ein Abzug der Datenbank ist im
Klartext möglich (nicht nur als Abbild einer laufenden Datei), und der Stapelordner
liegt dort, wo die Sicherung des Wirts ihn erfasst.

#### Scenario: Abzug der Datenbank
- **Ablauf:** Abzug erzeugen, Wiederherstellung in einer Probe beschreiben
- **Ergebnis:** Ein benannter Befehl liefert eine abspielbare Sicherung samt Vorgehen im Notfall
