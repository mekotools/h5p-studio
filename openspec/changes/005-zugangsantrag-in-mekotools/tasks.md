# Aufgaben — Change 005: Zugangsantrag nach MekoTools

## 1. Vorbereitung

- [x] 1.1 Bestandsaufnahme: Antrag liegt nur im Studio; Ablage leer (0 Anträge)
- [ ] 1.2 Entscheidung einholen: Technik des Dienstes, Bestätigungsmail,
      Umfang (nur Zugang oder auch Stufenaufstieg)
- [ ] 1.3 Formularworte und Pflichtfelder aus dem Studio übernehmen
      (`ZugangsantragForm` als Vorlage für Text und Prüfungen)

## 2. Antragsdienst

- [ ] 2.1 Kleiner Dienst mit zwei Seiten: Formular (GET) und Bestätigung (POST)
- [ ] 2.2 Schreibt in die vorhandene Tabelle `mekotools_studio_zugangsantrag`
      (gleiche Spalten wie das Studio-Formular)
- [ ] 2.3 Schutz: Honigtopf, Mindestzeit, Höchstzahl je Absender/Tag,
      Formatprüfung; keine fremden Abrufe (kein Captcha-Dienst)
- [ ] 2.4 Deutsche Beschriftungen, Gestalt der Hülle (Hausfarbe, hell/dunkel,
      mobil einreihig) — dieselbe Sprache wie Katalog und Studio

## 3. Einbinden

- [ ] 3.1 Traefik-Router `mekotools.de/antrag/` (eigener Behälter auf flip)
- [ ] 3.2 Studio: `/zugang` dauerhaft umleiten, Menüpunkt zeigt nach MekoTools
- [ ] 3.3 Katalog: Eintrag H5P-Studio auf die neue Adresse richtigstellen
- [ ] 3.4 Verwaltung im Studio unverändert lassen (Freigabe + Einladung)

## 4. Nachweis

- [ ] 4.1 Probelauf: Antrag abschicken → erscheint in der Studio-Verwaltung →
      freigeben → Einladung kommt an → Konto anlegbar (echter Durchlauf)
- [ ] 4.2 Gegenproben: Honigtopf greift, zu schnelles Absenden greift,
      fehlerhafte E-Mail wird abgewiesen, Mengenbegrenzung greift
- [ ] 4.3 Mobil und hell/dunkel nachmessen (390 px, Überlauf 0)
- [ ] 4.4 Fertigungslauf grün, ausliefern, Fingerabdruck notieren

## 5. Hausordnung

- [ ] 5.1 README und Katalog-Anleitung fortschreiben
- [ ] 5.2 Lehren in Skill festhalten (Schnittstelle zwischen zwei Behältern,
      gemeinsame Tabelle, Schutz offener Formulare)
