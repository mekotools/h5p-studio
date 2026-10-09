# Anforderung: MekoTools-Hülle (Change 003)

## Anforderung: Kein Fremdgerüst

Das Studio erscheint als Teil von MekoTools. Beim Aufruf ist kein Drupal-Standard
sichtbar: keine Begrüßungstexte des Kerns, kein Verweis auf die
Drupal-Gemeinschaft, kein fremdes Farbschema.

### Szenario: Startseite zeigt den Katalog

- **Angenommen** jemand ruft `https://studio.mekotools.de/` ohne Anmeldung auf
- **Dann** antwortet der Dienst mit 200
- **Und** die Seite zeigt die Bibliothek mit den Beispielen
- **Und** im Quelltext steht weder „Sie haben noch keinen Inhalt für die
  Startseite erstellt" noch „Komm wegen des Codes, bleib wegen der Community"
- **Und** die Titelzeile des Browsers lautet „Bibliothek | H5P-Studio"

### Szenario: Gestalt des Hauses

- **Angenommen** der Katalog wird geladen
- **Dann** trägt die Kopfleiste die Hausfarbe `#009485`
- **Und** die Fußzeile nennt MekoTools und verweist auf `https://mekotools.de/`
- **Und** es wird keine Schriftart und kein Skript von einem fremden Server geladen
- **Und** die Darstellung folgt der Systemeinstellung (hell oder dunkel)

### Szenario: Rückweg bleibt offen

- **Angenommen** jemand steht in der Bibliothek
- **Dann** führt der Kopfbereich zurück zu `https://mekotools.de/`
- **Und** ein weiterer Verweis führt zum Werkzeugkatalog

## Anforderung: Der Editor bleibt erreichbar

Die Hülle versteckt das Bauen von Inhalten nicht, sie ordnet es nur ein.

### Szenario: Anlegen bleibt anmeldepflichtig

- **Angenommen** jemand ist nicht angemeldet
- **Dann** ist der Katalog lesbar und Beispiele sind abspielbar
- **Und** der Weg zum Anlegen führt zur Anmeldung (Stufe 1 genügt)

## Anforderung: Einrichtung ist nachbaubar

Die Hülle ist Code, nicht Handarbeit in einer Datenbank.

### Szenario: Neue Instanz hat dieselbe Hülle

- **Angenommen** die Instanz wird aus dem Abbild neu aufgebaut
- **Dann** setzt der Aktualisierungsschritt `mekotools_studio_update_10005`
  Gerüst, Startseite und Menü
- **Und** der Schritt ist wiederholbar, ohne Schaden anzurichten

## Anforderung: Kein Verlust bestehender Arbeit

### Szenario: Inhalte bleiben abspielbar

- **Angenommen** die Hülle ist ausgeliefert
- **Dann** sind alle vorhandenen Inhalte unverändert in der Bibliothek
- **Und** die Knotenadressen antworten weiterhin mit 200 und betten H5P ein
- **Und** die Rechte der drei Stufen sind unverändert
