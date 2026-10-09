# Aufgaben — Change 003: MekoTools-Hülle

## 1. Entwurf

- [x] 1.1 Gestalt von mekotools.de abgelesen (MkDocs Material, Hausfarbe `#009485`)
- [x] 1.2 Grundlage des Gerüsts gewählt (`stark` statt Olivero), begründet
- [x] 1.3 Einrichtung als Aktualisierungsschritt festgelegt (nachbaubar, nicht Handarbeit)
- [ ] 1.4 Entscheidung zum Zugangsantrag einholen (Wege A/B/C, Empfehlung C)

## 2. Gerüst `mekotools_huelle`

- [x] 2.1 `mekotools_huelle.info.yml` (Grundlage `stark`, Bereiche: Kopf, Haupt, Fuß, Hinweise)
- [x] 2.2 Zeichen des Hauses (Werkzeugkasten) und Favicon aus dem Katalog-Repo übernommen
- [x] 2.3 `templates/page.html.twig`: Kopfleiste mit Hausname, Werkzeugname, Navigation, Rückweg
- [x] 2.4 Fußzeile ohne „Powered by Drupal": Haushinweis, Lizenz, Stand
- [x] 2.5 `css/huelle.css`: Hausfarbe, Breite, Abstände, Formulare, Tabellen, Meldungen
- [x] 2.6 Helle und dunkle Darstellung nach Systemeinstellung, keine fremden Schriftarten
- [x] 2.7 Schmale Geräte: Navigation klappt, Tafel rollt seitwärts statt abzuschneiden

## 3. Einrichtung im Modul

- [x] 3.1 `mekotools_studio_update_10005`: Gerüst auf `mekotools_huelle` setzen
      (über `theme_installer` + Konfiguration — `ThemeHandler::setDefault()` gibt
      es in Drupal 11 nicht mehr)
- [x] 3.2 Startseite auf `/bibliothek` setzen (Drupal-Begrüßung verschwindet)
- [x] 3.3 Hauptmenü: „Bibliothek" und „Werkzeugkatalog"; der Rückweg zu MekoTools
      sitzt als Knopf in der Kopfleiste, Drupals „Startseite"-Verweis wird beim
      Sammeln der Menüverweise übergangen
- [x] 3.4 Drupals eigene Standardblöcke des neuen Gerüsts entfernt (7 Stück,
      darunter „Angetrieben von Drupal", doppelte Marke und Hilfe-Block)
- [x] 3.5 Fünf Blöcke gesetzt: Seitentitel, Inhalt, Meldungen, Pfadnavigation,
      Hauptmenü. Bewusst nicht „Prüfschritt im Modul", sondern eine feste Ordnung,
      die bei jedem Lauf hergestellt wird (wiederholbar).

## 4. Bau und Auslieferung

- [ ] 4.1 Gerüst ins Abbild aufnehmen (`Dockerfile`), Stand im Repo
- [ ] 4.2 Fertigungslauf grün, Marke ablesen, ausliefern
- [ ] 4.3 `/bibliothek` und Startseite 200, keine Drupal-Begrüßungstexte mehr im Quelltext

## 5. Abnahme

- [x] 5.1 Startseite von außen: liefert den Katalog, Titelzeile „Bibliothek | H5P-Studio"
- [x] 5.2 Kein „Powered by Drupal", kein „Komm wegen des Codes, bleib wegen der Community"
- [x] 5.3 Kopfleiste trägt `#009485` (im gelieferten Gestaltungsblatt nachgewiesen),
      Rückweg zu mekotools.de vorhanden
- [ ] 5.4 Helle und dunkle Darstellung am laufenden Dienst geprüft
- [x] 5.5 Beispiele abspielen (node/20, node/30: je 200 mit H5P-Einbettung)
- [ ] 5.6 Anleitung im Repo um die Hülle ergänzen

## 6. Zwei Fehler, die dabei gefunden wurden (nicht gesucht)

Beide waren schon vorher da und fielen erst auf, weil die Hülle den Blick auf die
Bibliothek gelenkt hat. Beide sind behoben und am laufenden Dienst gemessen.

- [x] 6.1 **Die Bibliothek hatte keine Suchfelder.** Das Formular wurde als
      `#markup` zurückgegeben; Drupal filtert diesen Wert und wirft dabei
      `<form>`, `<label>`, `<input>`, `<select>` und `<button>` weg — übrig
      blieben die reinen Beschriftungen „Suche Fach Anzeigen". Gemessen:
      `['#markup' => '<b>fett</b> <input type="text">']` liefert nur
      `<b>fett</b>`. Behoben mit `Markup::create()` (die Werte im Formular sind
      ohnehin einzeln mit `Html::escape()` entschärft). Jetzt vorhanden und
      wirksam: `?s=Fake` → 8 Inhalte, `?s=Chat` → 3.
- [x] 6.2 **Die Bibliothek zählte nur die aktuelle Seite.** Die Anzeige meldete
      „25 Inhalte", obwohl 30 im Haus liegen, und auf der zweiten Seite
      „5 Inhalte". Ursache: `->pager(25)` hing an der Abfrage, die zum Zählen
      benutzt wurde — eine Kopie half nicht, die Blätterung wird bei jeder
      Ausführung angewendet. Jetzt wird gezählt, BEVOR die Blätterung gesetzt
      wird: **30 Inhalte**, auf beiden Seiten.
- [x] 6.3 Nebenbefund und Ursache einer längeren Sucherei: Das Modul lag im Abbild
      **verschachtelt** (`modules/custom/mekotools_studio/mekotools_studio/`),
      weil das Dockerfile den ganzen Ordner `studio/` in den Modulordner kopierte.
      Drupals Aktualisierungsverwaltung suchte die `.install`-Datei deshalb an der
      falschen Stelle und meldete „No database updates required" — der Schritt
      10005 wäre stillschweigend nie gelaufen. Behoben im Dockerfile; danach
      findet `drush updatedb` den Schritt. Merksatz: bei einem Modul ohne
      Fassungsnummer im `.info.yml` immer nachsehen, ob der Schritt überhaupt
      angeboten wird, statt „No database updates required" zu glauben.

## 7. Eine Umgebung statt vier Oberflächen (Anmeldung)

Die Nahtstellen zwischen Katalog (mekotools.de), Anmeldedienst (Pocket ID),
Sperre (Tinyauth) und Studio sollen aussehen wie **eine** Anwendung. Am
09.10.2026 umgesetzt und am laufenden Dienst gemessen:

- [x] 7.1 **Pocket ID trägt das Haus.** Name `MekoTools`, Hausfarbe (`teal`),
      Hauszeichen als Bild der Anmeldeseite, Favicon, Mailbild. Die Bilder liegen
      nicht in der Konfiguration, sondern hinter
      `/api/application-images/{logo,favicon,email}`; der Schreibweg ist
      `PUT` **multipart mit dem Feld `file`** (rohe Daten → 400).
- [x] 7.2 **Anmeldekunden heißen nach dem Werkzeug, nicht nach dem Dienst.** Auf
      der Zustimmungsseite steht „Sign in to H5P-Studio" bzw. „Sign in to
      MekoTools" (vorher „Tinyauth"). Umbenannt über
      `PUT /api/oidc/clients/{id}`; die Kennung bleibt gleich, Anmeldungen
      laufen weiter. Jeder Kunde trägt zusätzlich das Hauszeichen
      (`POST /api/oidc/clients/{id}/logo`) — sonst zeigt Pocket ID einen
      Buchstaben. Nach der Anmeldung führt der Rückweg auf mekotools.de.
- [x] 7.3 **Tinyauth trägt das Haus.** `TINYAUTH_UI_TITLE=MekoTools` und ein
      eigener Hintergrund. Eigene Dateien liegen unter `/data/resources` und sind
      **unter `/resources/…`** erreichbar (nicht unter dem nackten Namen — der
      erste Versuch landete in der Oberfläche, HTTP 200 mit HTML statt Bild).
- [x] 7.4 **Der Anmelde-Weg sitzt in der Kopfleiste des Studios** und führt
      direkt zu Pocket ID; „Abmelden" erscheint, wenn jemand angemeldet ist.
      Die Strecke `/openid-connect/{kunde}/initiate` des Moduls ist dafür
      **nicht** geeignet: sie verlangt `?iss=` und antwortet sonst mit 403
      (nur für den Anlauf vom Anmeldedienst selbst). Richtig ist Drupals
      Zwischenseite `/user/login/openid_connect`; `js/anmeldung.js` reicht sie
      in einem Zug weiter, wenn dort genau ein Anbieter steht. Ohne JavaScript
      bleibt der Knopf — kein stiller Fehler.
- [x] 7.5 Drupals eigene Anmeldeseite `/user/login` wird **nicht** umgeleitet:
      dort meldet sich die Verwaltung mit einem lokalen Konto an. Der Umbiegen
      hätte den Notfallweg genommen.

