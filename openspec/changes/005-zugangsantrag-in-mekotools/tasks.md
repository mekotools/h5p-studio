# Aufgaben — Change 005: Eine Zugangsleiter (Stufen statt Studio-Antrag)

## 1. Stufen wirklich vergeben

- [x] 1.1 Gemessen, welche Stufe welches Werkzeug verlangt (Pocket-ID-Anwendungen
      + Tinyauth-Regeln): Stufe 1 für Studio/Claper/Fuiz, Stufe 2 für Shadowbroker
- [x] 1.2 `FreigabeForm`: vergibt die beantragte Stufe (`Stufen::FACHKRAFT`) **und
      alles darunter** (Stufe 1 + 2), weil Werkzeuge ihre Mindeststufe einzeln prüfen
- [x] 1.3 Meldungen und Protokollzeilen sprechen von der Stufe, nicht von „Stufe 1"
- [x] 1.4 `Stufen.php` unverändert (die Leiter war schon richtig beschrieben)

## 2. Worte im Antragsweg

- [x] 2.1 Streckentitel: „Zugang als Lehrkraft beantragen" (`routing.yml`)
- [x] 2.2 Menüpunkt „Zugang zu MekoTools"; Beschreibung nennt Grundzugang und Antrag
- [x] 2.3 Einleitung: Grundzugang kostenlos ohne Antrag (Studio, Claper, Fuiz) →
      dieser Antrag für den bestätigten Zugang (Lehrkraft, z. B. Shadowbroker)
- [x] 2.4 Bestätigungsseite: „Dein Antrag auf den bestätigten Zugang liegt vor",
      Weg danach „Weiter zu MekoTools"
- [x] 2.5 Verwaltungsbeschreibung: „Anträge: bestätigter Zugang"

## 3. MekoTools-Seite im Katalog

- [x] 3.1 `docs/zugang.md`: die drei Stufen mit Bezeichnung und Gruppe, welche
      Werkzeuge je Stufe offen sind, Anmeldung über Passkey oder E-Mail-Code,
      wer einen Zugang bekommt, was Lernende brauchen (nichts)
- [x] 3.2 Katalog-Navigation: „Zugang zu MekoTools" hinter dem Werkzeugkatalog

## 4. Falsche Angaben berichtigen

- [x] 4.1 `tool.yaml`: für das Studio genügt Stufe 1, kein Antrag; Antrag nur für
      Stufe 2; richtige Adresse `/zugang` (statt `/zugang/antrag`, das 404 liefert)
- [x] 4.2 `docs/anleitung.md`: Grundzugang per Anmeldung, bestätigter Zugang per
      Antrag, richtige Adressen
- [x] 4.3 `herkunft.md`/`herkunft.csv` in der Anleitung absolut verlinkt — im
      Katalog waren die Verweise tot und der strenge Bau brach ab
- [x] 4.4 Katalog neu bauen

## 5. Nachweis

- [ ] 5.1 Gemessen: `/zugang` liefert 200, Titel „Zugang als Lehrkraft
      beantragen", Einleitung nennt Grundzugang und bestätigten Zugang
- [ ] 5.2 Katalogseite online erreichbar; die Verweise (Studio, Anmeldung,
      Stufen-Übersicht) liefern 200
- [ ] 5.3 Probelauf der Freigabe: Antrag → in der Verwaltung sichtbar → freigeben
      → Konto hat Stufe 2 **und** Stufe 1 (gegen den Anmeldedienst geprüft)
- [ ] 5.4 Kein zweiter Antrag: genau eine Strecke (`/zugang`)
- [ ] 5.5 Fertigungslauf grün, ausgeliefert, Fingerabdruck notieren

## 6. Hausordnung und offener Rest

- [ ] 6.1 README fortschreiben
- [ ] 6.2 Skill: Worte gehören dem Haus; eine Antragsstrecke; Freigabe vergibt
      Stufe samt Unterbau
- [ ] 6.3 **Entscheidung des Nutzers:** Selbstregistrierung in Pocket ID
      einschalten (`allowUserSignups`)? Derzeit aus — der Grundzugang ist damit
      noch nicht „ohne Prüfung" offen.
