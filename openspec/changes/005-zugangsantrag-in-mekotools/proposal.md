# Change 005 — Zugangsantrag nach MekoTools

## Warum

Der Zugang zu MekoTools gilt für **alle** Bereiche (Studio, Shadowbroker, Fuiz,
Claper …). Der Antrag darf deshalb nicht in einem einzelnen Werkzeug hängen —
trotzdem liegt er heute ausschließlich im Studio.

Bestandsaufnahme am 09.10.2026:

- Studio: Formular `/zugang` → Ablage `mekotools_studio_zugangsantrag` → Freigabe
  in der Verwaltung → Einladung per E-Mail.
- **Ablage leer: 0 Anträge.** Das Formular wurde noch nie benutzt.
- mekotools.de ist eine statische Seite (MkDocs) und kann kein Formular
  entgegennehmen. Im Katalog steht beim H5P-Studio derzeit sogar, man beantrage
  den Zugang „im Studio selbst" — also genau das, was nicht gewünscht ist.

Auftrag (Wortlaut der Nutzerin/des Nutzers vom 09.10.2026):

> „Der Antrag auf Zugang zu MekoTools erfolgt nicht in Drupal / H5P-Studio
> sondern in MekoTools, da er ja auch für andere Bereiche wie Shadowbroker gilt."

Nachfrage vom 09.10.2026: „Zugang beantragen ist doch jetzt in MekoTools oder,
wofür brauchen wir das noch in Drupal?" — Die Antwort war: **nein, ist es
nicht.** Dieser Change holt das nach.

## Was sich ändert

1. **Eigener Antragsdienst** unter der MekoTools-Adresse
   (`mekotools.de/antrag/`), über Traefik auf flip erreichbar, ohne Anmeldung —
   wer noch kein Konto hat, kann sich ja nicht anmelden.
2. **Dieselbe Ablage.** Der Dienst schreibt in die vorhandene Tabelle
   `mekotools_studio_zugangsantrag`. Vorteil: Die **Freigabe** bleibt unverändert
   in der Studio-Verwaltung (dort werden Stufen und Einladungen vergeben), und
   es gibt keine zweite Wahrheit über offene Anträge.
3. **Das Studio gibt das Formular ab.** `/zugang` leitet auf die neue Adresse um
   (301, keine Doppelpflege); der Menüpunkt „Zugang beantragen" zeigt dorthin.
   Die Verwaltungsseite `/verwaltung/zugangsantraege` bleibt.
4. **Der Katalog wird richtiggestellt.** Im Werkzeug-Eintrag H5P-Studio steht
   künftig die MekoTools-Adresse des Antrags, nicht die Studio-Adresse.
5. **Schutz gegen Missbrauch**, weil das Formular offen im Netz steht:
   Honigtopf-Feld, Mindestzeit zwischen Aufruf und Absenden, Höchstzahl je
   Absender und Tag, Längen- und Formatprüfung (E-Mail, Name, Einrichtung).
   Kein Captcha eines fremden Anbieters (Abrufe nach außen vermeiden).

## Offene Entscheidungen (vor dem Bau zu klären)

- **Sprache/Technik des Dienstes:** kleines Python-Programm (Hausbrauch) oder
  PHP im Studio-Abbild? Empfehlung: Python, ein Behälter, eigener Name.
- **Bestätigungsmail an Antragstellende** („Antrag ist eingegangen") — sinnvoll,
  kostet aber eine Mailvorlage. Empfehlung: ja, eine schlichte Mail.
- **Zweiter Weg für Bestandskonten:** Soll der Antrag auch für Personen offen
  sein, die schon ein Konto haben (Stufenaufstieg „angemeldet" → „Fachkraft")?
  Heute ist es ein Zugangsantrag. Empfehlung: jetzt nicht mischen.

## Grenzen

- Die Ablage bleibt technisch im Studio-Abbild (Tabelle dort). Wer das radikal
  trennen will (kein Werkzeug hat Sonderrechte), braucht Change 006: eigene
  Datenbank und eigenes Verwaltungsfenster außerhalb des Studios.
- Kein Antrag wird automatisch freigegeben. Jede Freigabe bleibt eine
  menschliche Entscheidung einer Verwaltungskraft.
