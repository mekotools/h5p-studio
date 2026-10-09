# Aufgaben — Change 005: Eine Antragsstrecke (Zugang zu MekoTools)

## 1. Worte im Antragsweg

- [x] 1.1 Streckentitel: „Zugang zu MekoTools beantragen"
      (`mekotools_studio.routing.yml`)
- [x] 1.2 Menüpunkt: „Zugang zu MekoTools", Beschreibung „ein Zugang gilt für
      alle Werkzeuge" (`mekotools_studio.links.menu.yml`)
- [x] 1.3 Formulareinleitung: „MekoTools steht Lehrkräften und Fachkräften …
      offen. Ein Zugang gilt für alle Werkzeuge — das H5P-Studio, Shadowbroker
      und alles, was noch dazukommt." (`ZugangsantragForm`)
- [x] 1.4 Bestätigungsseite: „Dein Antrag auf Zugang zu MekoTools liegt vor",
      „Der Zugang gilt dann für alle MekoTools-Werkzeuge", Weg danach
      „Weiter zu MekoTools" statt „Zur Bibliothek" (`ZugangController`)
- [x] 1.5 Verwaltungsbeschreibung: „Anträge auf Zugang zu MekoTools einsehen und
      freigeben" (`mekotools_studio.links.menu.yml`, Abschnitt admin)

## 2. MekoTools-Seite im Katalog

- [x] 2.1 Neue Seite `docs/zugang.md`: ein Zugang für alle Werkzeuge, Weg zum
      Formular, Ablauf, wer einen Zugang bekommt, was Lernende brauchen (nichts)
- [x] 2.2 Katalog-Navigation: Eintrag „Zugang zu MekoTools" hinter dem
      Werkzeugkatalog

## 3. Falschen Verweis berichtigen

- [x] 3.1 `tool.yaml` im Werkzeug-Repo: „Zugang zu MekoTools beantragt man unter
      studio.mekotools.de/zugang" (statt `/zugang/antrag`, das 404 liefert)
- [x] 3.2 `docs/anleitung.md`: „Einen Zugang zu MekoTools … gilt für alle
      Werkzeuge" + richtige Adresse
- [x] 3.3 Katalog neu bauen, damit Werkzeugtafel und Werkzeugseite die neuen
      Texte tragen

## 4. Nachweis

- [ ] 4.1 Gemessen: `/zugang` liefert 200, Überschrift und Einleitung sagen
      „MekoTools" (nicht „Studio"), Menüpunkt heißt „Zugang zu MekoTools"
- [ ] 4.2 Katalogseite „Zugang zu MekoTools" online erreichbar; ihr Verweis führt
      auf das Formular (HTTP 200)
- [ ] 4.3 Kein zweiter Antrag: genau eine Strecke (`/zugang`), überall dieselbe
- [ ] 4.4 Fertigungslauf grün, ausgeliefert, Fingerabdruck notieren

## 5. Hausordnung

- [ ] 5.1 README des Werkzeug-Repos fortschreiben
- [ ] 5.2 Lehre im Skill festhalten: Worte gehören dem Haus, nicht dem einzelnen
      Werkzeug; ein Zugang, eine Strecke
