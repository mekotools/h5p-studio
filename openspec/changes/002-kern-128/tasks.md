# Aufgaben — Change 002: H5P-Kern 1.28

## 1. Entwurf (erledigt)

- [x] 1.1 Schnittstellenunterschied 1.27 → 1.28 belegt (`H5PFrameworkInterface`, genau eine Methode)
- [x] 1.2 Modulzweig `2.0.x` geprüft: Methode fehlt auch dort
- [x] 1.3 Betroffene Pakete gezählt: 12 von 30 verlangen `coreApi` 1.28
- [x] 1.4 Wege A–D abgewogen, Weg D (eigene Flickdatei im Bau) gewählt

## 2. Flickdatei

- [ ] 2.1 `docker/flickwerk/h5p-resethuborganizationdata.patch` anlegen
      (Ziel: `web/modules/contrib/h5p/src/H5PDrupal/H5PDrupal.php`, Bezug `-p1`)
- [ ] 2.2 Flickdatei in der Baustufe anwenden
- [ ] 2.3 `php -l` auf die geflickte Datei

## 3. Bauplan

- [ ] 3.1 Kernnagel von `1.27.0` auf `1.28.0` heben (Begründungstext mitziehen)
- [ ] 3.2 Alte Sperre entfernen („`resetHubOrganizationData` darf NICHT vorkommen")
- [ ] 3.3 Neue Prüfungen ergänzen: Kern 1.28 liegt vor, Flickdatei sitzt, `php -l` sauber
- [ ] 3.4 Bauprobe örtlich (Behälter mit den neuen Zeilen) — erst danach Fertigung

## 4. Fertigung und Auslieferung

- [ ] 4.1 Commit + Push (CI bricht bei Verstoß ab)
- [ ] 4.2 Fertigungslauf grün
- [ ] 4.3 Marke ablesen (voller Commit-Wert), Stapel auf die neue Marke ziehen
- [ ] 4.4 Gesundheitsprobe: `/bibliothek` antwortet 200, Behälter bleibt oben

## 5. Abnahme am echten Paket

- [ ] 5.1 Ein Paket mit `coreApi` 1.28 aufnehmen (Probe: „Urheberrechte")
- [ ] 5.2 Im Protokoll „H5P-Inhalt angelegt: id=…" nachweisen
- [ ] 5.3 Knoten von außen aufrufen, Abspielen belegen
- [ ] 5.4 Erst dann die restlichen 29 Beispiele aufnehmen

## 6. Nachziehen

- [ ] 6.1 Herkunftsnachweis der Beispiele schreiben (Quelle, Urheber, Lizenz, Prüfsumme)
- [ ] 6.2 Doku des Studios um den Katalogbestand ergänzen
- [ ] 6.3 Flickdatei dem Modul anbieten (eigener Vorgang, nachrichtlich vormerken)
