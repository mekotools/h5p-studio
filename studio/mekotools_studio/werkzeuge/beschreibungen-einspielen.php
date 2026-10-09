<?php

/**
 * @file
 * Setzt die Beschreibungen der Bibliotheksbeispiele.
 *
 * Aufruf im Behälter:
 *   drush php:script /opt/drupal/web/modules/custom/mekotools_studio/werkzeuge/beschreibungen-einspielen.php
 * Mit Überschreiben vorhandener Texte:
 *   drush php:script ... -- --ueberschreiben
 *
 * Warum als eigenes Werkzeug und nicht als Aktualisierungsschritt: Das sind
 * Inhalte. Ein Aktualisierungsschritt liefe bei jedem Nachbau und würde dabei
 * Texte überschreiben, die jemand im Studio von Hand gepflegt hat.
 */

$ueberschreiben = in_array('--ueberschreiben', $extra ?? [], TRUE)
  || in_array('ueberschreiben', $extra ?? [], TRUE);

$quelldatei = __DIR__ . '/beschreibungen.json';
if (!is_readable($quelldatei)) {
  print "Quelldatei fehlt: $quelldatei\n";
  return;
}
$daten = json_decode(file_get_contents($quelldatei), TRUE);
$texte = $daten['eintraege'] ?? [];
$kennung = $daten['_hinweis'] ?? '';

$speicher = \Drupal::entityTypeManager()->getStorage('node');
$knoten = $speicher->loadByProperties(['type' => 'h5p_inhalt']);

$gesetzt = 0;
$uebersprungen = 0;
$offen = [];

foreach ($knoten as $eintrag) {
  $titel = $eintrag->label();
  if (!isset($texte[$titel])) {
    $offen[] = $titel;
    continue;
  }
  $vorhanden = (string) ($eintrag->get('field_beschreibung')->value ?? '');
  if ($vorhanden !== '' && !$ueberschreiben) {
    $uebersprungen++;
    continue;
  }
  if ($vorhanden === $texte[$titel]) {
    $uebersprungen++;
    continue;
  }
  $eintrag->set('field_beschreibung', $texte[$titel]);
  $eintrag->setNewRevision(FALSE);
  $eintrag->save();
  $gesetzt++;
}

// Die Texte sind nicht ausgedacht, sondern aus den Paketen erhoben — die
// Herkunft steht als Notiz in der Quelldatei und wird hier mitgemeldet.
print "Beschreibungen gesetzt: $gesetzt\n";
print "Unverändert: $uebersprungen\n";
if ($offen) {
  print "Ohne Text in der Quelldatei (" . count($offen) . "): " . implode(' | ', $offen) . "\n";
}
if ($kennung !== '') {
  print "Herkunft der Texte: $kennung\n";
}
