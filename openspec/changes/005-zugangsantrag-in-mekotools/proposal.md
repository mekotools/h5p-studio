# Change 005 — Eine Zugangsleiter: Stufen statt Studio-Antrag

## Warum

Für Nutzende sollen alle Apps **aus einem Guss** erscheinen. Wer Zugang will,
beantragt deshalb keinen Zugang „zum Studio", sondern Zugang zu **MekoTools** —
und zwar auf der Stufe, die er braucht.

Der Zugang ist eine **Leiter mit drei Stufen** (so auch schon im Code,
`src/Stufen.php`):

| Stufe | Gruppe | Bezeichnung | Wer kommt rein |
|---|---|---|---|
| 1 | `angemeldet` | Angemeldet (Grundzugang) | ohne Prüfung |
| 2 | `lehrkraefte` | Fachkraft bestätigt (Lehrkraft) | per Antrag, geprüft |
| 3 | `verwaltung` | Verwaltung | auf Einladung |

Am 09.10.2026 am laufenden Dienst gemessen, welche Werkzeug welche Stufe
verlangt:

- **Stufe 1** genügt für **H5P-Studio** (Pocket-ID-Anwendung „H5P-Studio",
  erlaubte Gruppe `angemeldet`), **Claper** (dito) und **Fuiz**
  (`TINYAUTH_APPS_FUIZSTASH_OAUTH_GROUPS=angemeldet`).
- **Stufe 2** verlangt **Shadowbroker**
  (`TINYAUTH_APPS_SHADOWBROKER_OAUTH_GROUPS=lehrkraefte`, Vorgabe
  `TINYAUTH_AUTH_ACLS_POLICY=deny`).

Was nicht stimmte:

- Die Antragsseite hieß **„Zugang zum Studio beantragen"** und sprach vom
  Studio — obwohl die Antragsstrecke MekoTools gehört.
- Die **Freigabe vergab Stufe 1** (`Stufen::ANGEMELDET`), nicht die beantragte
  Stufe. Wer für Shadowbroker freigegeben wurde, kam dort also nicht hinein.
- Anleitung und Werkzeug-Metadaten verwiesen auf
  `studio.mekotools.de/zugang/antrag` — **404** (richtig ist `/zugang`).
- Der Katalog schrieb, man beantrage den Zugang „im Studio selbst".

Auftrag (Wortlaut, 09.10.2026):

> „Für den User sollen alle Apps aus einem Guss erscheinen. D.h. man beantragt
> kein Zugang zu mekotools Studio, sondern zu mekotools. Wir können die
> Antragsseite auch aus Drupal von mekotools einbinden, aber wir brauchen keinen
> EXTRA Antrag von der Studio Seite aus."

> „Es gibt drei Level und permissions. Self register ist auch direkt ein
> registrierter User ohne Prüfung, für z.B. fuiz, claper … Beantragen muss man
> nur für den Level Lehrkraft, um z.B. Zugang zu Shadowbroker o.ä. zu erhalten."

Daraus folgt: **kein zweiter Antrag, kein eigener Dienst** — eine Strecke, ein
Name, und sie vergibt die Stufe, die beantragt wird. Die Ablage ist leer
(0 Anträge); es geht nichts verloren.

## Was sich ändert

1. **Die Freigabe vergibt die beantragte Stufe — samt allem darunter.**
   Der Antrag ist der Antrag auf den **bestätigten Zugang (Stufe 2)**. Die
   Freigabe setzt Stufe 2 und Stufe 1, weil die Werkzeuge ihre eigene
   Mindeststufe prüfen und Pocket-ID-Gruppen nicht mitwachsen. (`FreigabeForm`)
2. **Worte: MekoTools und Stufe statt Studio.** Titel „Zugang als Lehrkraft
   beantragen", Menüpunkt „Zugang zu MekoTools", Einleitung erklärt beide Wege
   (Grundzugang ohne Antrag / bestätigter Zugang per Antrag), Bestätigungsseite
   und Weg danach angepasst.
3. **MekoTools bekommt die Seite.** Im Werkzeugkatalog (mekotools.de) entsteht
   „Zugang zu MekoTools" mit der Stufenleiter, den Werkzeugen je Stufe, dem Weg
   zur Anmeldung, dem Antragsweg und der Auskunft, dass Lernende nichts
   brauchen.
4. **Falscher Verweis berichtigt.** Anleitung und Werkzeug-Metadaten nennen die
   richtige Adresse und die richtige Stufe (Studio: Stufe 1 genügt).

## Offener Rest (Entscheidung nötig)

- ~~**Selbstregistrierung ist derzeit aus.**~~ **Erledigt am 09.10.2026:** auf
  Nutzerentscheidung („Ja Selbstregistrierung an") eingeschaltet —
  `allowUserSignups=open` **und** `signupDefaultUserGroupIDs=[angemeldet]`. Der
  zweite Wert ist der wichtigere: ohne ihn landen selbst registrierte Konten in
  **keiner** Gruppe und haben wegen der Grundhaltung `deny` nirgends Zugang.
  Ende zu Ende belegt: Konto über `/signup` mit virtuellem Passkey angelegt →
  Gruppe `angemeldet` → Testkonto wieder gelöscht.
- **Bekannter Preis:** `emailVerificationEnabled=false` — eine Registrierung
  braucht keine bestätigte Adresse. Offen, ob das so bleiben soll.
- Die Seite läuft weiterhin unter der Adresse des Studios — die einzige
  verbleibende Naht. Eine eigene Adresse (`mekotools.de/antrag` oder
  `zugang.mekotools.de`) wäre ein eigener Change.
