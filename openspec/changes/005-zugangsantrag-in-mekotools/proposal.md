# Change 005 — Eine Antragsstrecke: Zugang zu MekoTools

## Warum

Für Nutzende sollen alle Apps **aus einem Guss** erscheinen. Wer Zugang will,
beantragt deshalb keinen Zugang „zum Studio", sondern **zu MekoTools** — einen
Zugang, der für alle Werkzeuge gilt (H5P-Studio, Shadowbroker, Fuiz …).

Heute steht das Gegenteil in der Welt:

- Die Seite heißt **„Zugang zum Studio beantragen"**, der Menüpunkt „Zugang
  beantragen", das Formular beginnt mit „Das Studio steht … offen".
- Die Anleitung im Werkzeug und der Werkzeugkatalog verweisen auf
  `studio.mekotools.de/zugang/antrag` — **das liefert 404** (richtig ist
  `/zugang`, am 09.10.2026 gemessen).
- Der Katalog schreibt, man beantrage den Zugang „im Studio selbst".

Auftrag (Wortlaut, 09.10.2026):

> „Für den User sollen alle Apps aus einem Guss erscheinen. D.h. man beantragt
> kein Zugang zu mekotools Studio, sondern zu mekotools. Wir können die
> Antragsseite auch aus Drupal von mekotools einbinden, aber wir brauchen keinen
> EXTRA Antrag von der Studio Seite aus."

Daraus folgt: **kein zweiter Antrag, kein eigener Dienst.** Die vorhandene
Strecke bleibt bestehen, wird aber zu der einen MekoTools-Strecke erklärt und
benannt. Bestandsaufnahme: die Ablage ist leer (0 Anträge), es geht also nichts
verloren.

## Was sich ändert

1. **Worte: MekoTools statt Studio.** Seitentitel, Menüpunkt, Einleitungstext des
   Formulars, Bestätigungsseite („Dein Antrag auf Zugang zu MekoTools liegt
   vor") und der Weg danach („Weiter zu MekoTools" statt „Zur Bibliothek") —
   denn nach dem Antrag geht es nicht um das Studio, sondern um das Haus.
2. **Eine Strecke, ausdrücklich.** Der Antrag bleibt technisch in Drupal
   (Drupal kann ein Formular entgegennehmen, die Hauptseite des Katalogs ist
   statisch), ist aber die **einzige** Antragsstrecke. Es entsteht keine zweite.
3. **MekoTools bekommt die Seite.** Im Werkzeugkatalog (mekotools.de) entsteht
   die Seite „Zugang zu MekoTools": ein Zugang für alle Werkzeuge, Weg zum
   Formular, Ablauf (prüfen → Einladung → Passkey), wer einen Zugang bekommt,
   was Lernende brauchen (nichts).
4. **Falscher Verweis berichtigt.** Anleitung und Werkzeug-Metadaten nennen
   künftig die richtige Adresse (`studio.mekotools.de/zugang`).

## Grenzen und offener Rest

- Die Seite läuft weiterhin unter der Adresse des Studios — das ist die einzige
  verbleibende Naht. Wer sie schließen will, braucht einen eigenen Change
  (eigene Adresse für die Antragsseite, etwa `mekotools.de/antrag` oder
  `zugang.mekotools.de` über Traefik). Nicht Teil dieses Changes: es kostet
  Maschinerie, solange die Seite selbst „MekoTools" sagt.
- Freigabe und Einladung bleiben in der Studio-Verwaltung (dort werden Stufen
  und Konten vergeben). Das ist kein zweiter Antrag, sondern die Bearbeitung.
