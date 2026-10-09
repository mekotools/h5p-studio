# Aufgaben — Change 002: H5P-Kern 1.28

## 1. Entwurf (erledigt)

- [x] 1.1 Schnittstellenunterschied 1.27 → 1.28 belegt (`H5PFrameworkInterface`, genau eine Methode)
- [x] 1.2 Modulzweig `2.0.x` geprüft: Methode fehlt auch dort
- [x] 1.3 Betroffene Pakete gezählt: 12 von 30 verlangen `coreApi` 1.28
- [x] 1.4 Wege A–D abgewogen, Weg D (eigene Flickdatei im Bau) gewählt

## 2. Flickdatei

- [x] 2.1 `docker/flickdateien/h5p-resethuborganizationdata.patch` angelegt
      (Ziel: `web/modules/contrib/h5p/src/H5PDrupal/H5PDrupal.php`, Bezug `-p1`;
      entstand aus echtem Vergleich per `difflib`, nicht aus dem Gedächtnis —
      der erste, von Hand geschriebene Versuch scheiterte im Trockenlauf bei
      Zeile 1409)
- [x] 2.2 Flickdatei in der Baustufe angewendet
- [x] 2.3 `php -l` auf die geflickte Datei

## 3. Bauplan

- [x] 3.1 Kernnagel von `1.27.0` auf `1.28.0` gehoben (Begründungstext mitgezogen)
- [x] 3.2 Alte Sperre entfernt („`resetHubOrganizationData` darf NICHT vorkommen“)
- [x] 3.3 Neue Prüfungen ergänzt: Kern 1.28 liegt vor, Flickdatei sitzt, `php -l` sauber
- [x] 3.4 Bauprobe örtlich (Behälter mit den neuen Zeilen) — erst danach Fertigung

## 4. Fertigung und Auslieferung

- [x] 4.1 Commit `20a4ffa` + Push (CI bricht bei Verstoß ab)
- [x] 4.2 Fertigungslauf 39 grün (~100 s)
- [x] 4.3 Marke abgelesen und ausgeliefert:
      `sha256:1cc88e9f78a663fe4193520fac93ef35b330d06275e541dc0bf13c66da40758e`
- [x] 4.4 Gesundheitsprobe: `/bibliothek` 200, Startseite 200, Behälter gesund

## 5. Abnahme am echten Paket

- [x] 5.1 Ein Paket mit `coreApi` 1.28 aufnehmen — Probe war
      „Studienübersicht Mediennutzung in Deutschland“ (nid 1);
      „Urheberrechte“ lief am Ende als letztes Exemplar mit
- [x] 5.2 Im Protokoll „H5P-Inhalt angelegt: id=…“ nachgewiesen
- [x] 5.3 Knoten von außen aufgerufen (node/20, node/30): je 200 mit H5P-Einbettung
- [x] 5.4 Restliche 29 Beispiele aufgenommen — Stand **30 Knoten, 30 Inhalte,
      95 Bibliotheken**

## 6. Nachziehen

- [x] 6.1 Herkunftsnachweis geschrieben: `docs/herkunft.md` (lesbar) und
      `docs/herkunft.csv` (maschinenlesbar) — je Beispiel Urheber, Lizenz,
      Lizenzverweis, Quellseite, Abrufdatum, SHA-256 und Knotenadresse
- [x] 6.2 Doku des Studios um den Katalogbestand ergänzt (`docs/anleitung.md`,
      Abschnitt „Was schon drin ist“)
- [ ] 6.3 Flickdatei dem Modul anbieten (eigener Vorgang, nachrichtlich vormerken)

## Anmerkungen zur Umsetzung

- **Speichergrenze:** Der erste Aufnahmelauf starb nach 21 von 30 Exemplaren an
  der 128-MB-Grenze von PHP. Ursache war nicht die Menge, sondern die eigene
  Bauart: je Exemplar wurde eine neue Kern-Instanz erzeugt, die sich ansammelt.
  Behoben durch eine einzige Instanz für den ganzen Lauf; der Lauf bekommt
  zusätzlich `-d memory_limit=512M` mit.
- **H5P verbraucht die hochgeladene Datei.** Nach dem Einspielen ist die
  `.h5p`-Datei im Behälter gelöscht. Wer nachlegen will, muss die Datei erneut
  bereitstellen — sonst meldet das Protokoll „Datei fehlt“.
- **Zwischenstand je Exemplar sichern.** Der Bericht wird jetzt nach jedem
  Exemplar geschrieben, nicht erst am Ende; ein Abbruch kostet so keinen
  Fortschritt mehr.
