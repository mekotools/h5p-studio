# Design — Change 002: H5P-Kern 1.28

## Ausgangslage

| Baustein | Fassung | Anmerkung |
|---|---|---|
| `drupal/h5p` | 2.0.0-beta1 | neueste Veröffentlichung; `2.0.x-dev` hat die Methode ebenfalls nicht |
| `h5p/h5p-core` | 1.27.0 (festgenagelt) | Schnittstelle ohne `resetHubOrganizationData` |
| `h5p/h5p-editor` | über das Modul gezogen | unbeteiligt |
| `drupal/core` | 11.4.8 | unbeteiligt |

Der Unterschied der Schnittstelle 1.27 → 1.28 ist genau **eine** Methode
(`resetHubOrganizationData`). Sie wird von H5P aufgerufen, wenn der Hub
Zugangsdaten mit **403** abweist: die Ablage soll die hinterlegte Kombination aus
Organisation und Seitenkennung verwerfen, damit eine Neu-Anmeldung möglich ist.

## Betrachtete Wege

**A) Auf 1.27 bleiben, die 12 Pakete weglassen.** Verworfen. Es trifft genau die
inhaltlich stärksten Beispiele, und jede künftige Veröffentlichung verschärft das
Problem. Der Katalog wäre dauerhaft auf altem Stand.

**B) Auf den Nachtrag des Moduls warten.** Verworfen. Der Zweig `2.0.x` trägt die
Methode Stand 09.10.2026 nicht. Kein Datum, keine Zusage.

**C) Auf eine Vorabfassung `2.0.x-dev` umsteigen.** Verworfen. Ein
Entwicklungszweig in der Fertigung heißt: unangekündigte Änderungen im Betrieb.

**D) Eigene Flickdatei im Bau (gewählt).** Die Methode wird beim Bauen in das
Modul eingefügt. Sie ist klein, ihr Verhalten ist aus der Schnittstelle ableitbar,
und der Bau prüft anschließend ihr Vorhandensein. Das ist ehrlicher als ein
Entwicklungszweig: wir wissen genau, was wir hinzufügen, und können es zurücknehmen,
sobald das Modul nachzieht.

## Was die Flickdatei tut

```php
public function resetHubOrganizationData() {
  // Der Hub hat die Zugangsdaten abgewiesen: Seitenkennung verwerfen,
  // damit sich die Seite neu anmelden kann.
  $this->setOption('site_uuid', NULL);
  \Drupal::state()->delete('h5p.hub_organization', NULL);
}
```

Warum das genügt: die Seitenkennung (`h5p_site_uuid`) ist die einzige Kennung, die
diese Anbindung gegenüber dem Hub führt — ein Hub-Konto mit geheimem Schlüssel
gibt es hier nicht. Ein Zwischenstandsspeicher `h5p.hub_organization` existiert
nicht; das Löschen ist ein No-Op und schadet nicht. Wer die Methode aufruft, ist
der Hub-Weg (Veröffentlichen, Kontoinformation); der Weg
„Paket aufnehmen und abspielen" berührt sie nicht.

## Prüfungen (im Bau, nicht nur in der Hand)

1. `test -f vendor/h5p/h5p-core/h5p.classes.php` (Grundlage vorhanden)
2. `grep -q resetHubOrganizationData vendor/h5p/h5p-core/h5p.classes.php` —
   Kern 1.28 liegt wirklich vor.
3. `grep -q resetHubOrganizationData web/modules/contrib/h5p/src/H5PDrupal/H5PDrupal.php`
   — die Flickdatei ist wirklich angekommen.
4. `php -l` auf die geflickte Datei — kein Syntaxfehler.
5. Nach der Auslieferung: ein Paket mit `coreApi` 1.28 durch die echte Aufnahme
   schicken und im Protokoll „H5P-Inhalt angelegt: id=…" sehen.

## Offene Punkte

- Die Flickdatei dem Modul anbieten (Fehlerbericht + Änderungsvorschlag) — eigener
  Vorgang, nicht Teil dieses Changes.
- Wenn das Modul nachzieht: Flickdatei entfernen, Kernnagel auf `^1.28` lockern.
