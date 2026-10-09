## ADDED Requirements

### Requirement: Anmeldung über den MekoTools-Anmeldedienst

Der Zugang zum Studio läuft über Pocket ID (`auth.mekotools.de`) mit Passkey. Es gibt
kein Kennwort im Studio. Die Anmeldung muss auch dann funktionieren, wenn die
Verschlüsselung am VPS endet (Rücksprung `https://h5p-studio.mekotools.de/…`).

#### Scenario: Anmeldung einer Lehrkraft
- **Akteure:** Lehrkraft mit Pocket-ID-Konto
- **Ablauf:** Aufruf der Studio-Adresse → Weiterleitung zum Anmeldedienst → Passkey → Rückleitung in das Studio
- **Ergebnis:** Die Lehrkraft sieht die Bibliothek, nicht die Anmeldeseite

#### Scenario: Unbekannte Adresse wird nicht durchgelassen
- **Akteure:** Person ohne Konto
- **Ergebnis:** Sie kommt nicht in den Verwaltungsbereich; ein Zugriff auf einen Inhalt ohne Konto bleibt möglich (siehe `bibliothek`)

### Requirement: Konto entsteht bei der ersten Anmeldung

Bei der ersten Anmeldung über den Anmeldedienst entsteht im Studio ein Konto mit der
Adresse aus der Anmeldung. Ein zweites Konto für dieselbe Adresse entsteht dabei nicht.

#### Scenario: Erstes Anmelden
- **Eingaben:** Anmeldung mit einer Adresse, die im Studio noch nicht existiert
- **Ergebnis:** Genau ein Konto entsteht, es trägt die Anmeldeadresse, und die Anmeldung endet in der Bibliothek

#### Scenario: Zweites Anmelden
- **Eingaben:** dieselbe Adresse erneut
- **Ergebnis:** Kein zweites Konto; der vorhandene Bestand der Person ist unverändert vorhanden

### Requirement: Rollen und Rechte

Wer sich anmeldet, darf Inhalte anlegen, bearbeiten und die Bibliothek durchsuchen.
Verwaltungsrechte des Studios (Bibliotheken pflegen, Einstellungen des H5P-Moduls,
Konten) liegen bei der Administratorrolle, nicht bei jeder Lehrkraft.

#### Scenario: Lehrkraft legt Inhalt an
- **Ergebnis:** Erlaubt; die Einrichtung des Studios ist für sie nicht erreichbar

#### Scenario: Zugriff auf die Einrichtung
- **Akteure:** Lehrkraft
- **Ergebnis:** Abgewiesen (403), nicht mit einer stillen Weiterleitung

### Requirement: Notfallzugang

Es bleibt ein Konto mit Kennwort im Studio bestehen, damit ein Ausfall des
Anmeldedienstes die Instanz nicht unbedienbar macht. Das Kennwort steht ausschließlich
auf dem Zielrechner (Rechte 600) und wird nicht über den Chat weitergegeben.

#### Scenario: Anmeldedienst nicht erreichbar
- **Ablauf:** Die lokale Anmeldeseite des Studios wird aufgerufen
- **Ergebnis:** Anmeldung mit dem Notfallkonto ist möglich; der Weg wird in der Betriebsdokumentation benannt
