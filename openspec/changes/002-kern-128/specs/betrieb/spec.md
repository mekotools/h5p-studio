## ADDED Requirements

### Requirement: Aufnahmefähigkeit für neuere H5P-Inhalte

Das Studio MUSS H5P-Pakete aufnehmen können, die von neu veröffentlichten
Inhalten stammen. Maßstab ist nicht die Fassung des H5P-Kerns allein, sondern
das Verhalten: ein Paket, das eine höhere Kernfassung verlangt als die
installierte, darf nicht abgewiesen werden, solange die verlangte Fassung
innerhalb der vom Modul zugelassenen Spanne liegt (`h5p/h5p-core ^1.27`).

#### Scenario: Paket mit höherer Kernanforderung wird aufgenommen

- **Akteure:** Verwaltung (Konto mit Recht „update h5p libraries")
- **Eingaben:** eine `.h5p`-Datei, deren Bibliotheken `coreApi` 1.28 verlangen
- **Ergebnis:** Der H5P-Inhalt wird angelegt, benötigte Bibliotheken werden
  eingespielt, und es entsteht ein Knoten vom Typ `h5p_inhalt` mit Fachzuordnung.
  Die frühere Meldung „requires a newer version of the H5P plugin" tritt nicht
  mehr auf.

#### Scenario: Fehlende Kernfassung wird im Bau bemerkt, nicht im Betrieb

- **Akteure:** Fertigung (CI)
- **Eingaben:** Bauplan mit Kernnagel und Flickdatei
- **Ergebnis:** Der Bau prüft, dass die Kernfassung vorliegt und die
  nachgetragene Schnittstellenmethode im Modul vorhanden ist. Fehlt eines der
  beiden, bricht der Bau ab; ein unvollständiges Abbild wird nicht ausgeliefert.

#### Scenario: Nachgetragene Schnittstellenmethode ohne Wirkung auf den Abspielweg

- **Akteure:** Besucher, angemeldete Lehrkraft
- **Eingaben:** Aufruf von Bibliothek und Inhalt
- **Ergebnis:** Das Anzeigen und Abspielen von Inhalten ist von der
  nachgetragenen Methode unberührt; sie wird ausschließlich auf den Hub-Wegen
  aufgerufen, die diese Installation nicht benutzt.
