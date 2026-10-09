<?php

namespace Drupal\mekotools_studio\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mekotools_studio\Antragsspeicher;

/**
 * Bestätigungsseite nach dem Abschicken eines Zugangsantrags.
 */
class ZugangController extends ControllerBase {

  /**
   * Zeigt die Antragsnummer und was als Nächstes passiert.
   */
  public function erhalten() {
    $nummer = (int) \Drupal::request()->query->get('nummer');
    $speicher = new Antragsspeicher();
    $antrag = $nummer ? $speicher->laden($nummer) : NULL;

    $bau = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mekotools-studio-bestaetigung']],
    ];
    if (!$antrag) {
      $bau['text'] = ['#plain_text' => 'Diese Bestätigung passt zu keinem Antrag. Bitte schick das Formular noch einmal ab.'];
      return $bau;
    }
    $bau['danke'] = ['#plain_text' => 'Danke! Dein Antrag liegt vor.'];
    $bau['nummer'] = ['#plain_text' => 'Antragsnummer: ' . $antrag->nummer];
    $bau['naechstes'] = [
      '#plain_text' => 'Wir prüfen den Antrag und schicken dir eine Einladung per E-Mail. '
      . 'Mit dieser Einladung legst du einen Passkey an — ein Kennwort brauchst du nicht. '
      . 'Schau im Zweifel in den Spam-Ordner.',
    ];
    $bau['weiter'] = [
      '#type' => 'link',
      '#title' => 'Zur Bibliothek',
      '#url' => \Drupal\Core\Url::fromRoute('mekotools_studio.bibliothek'),
    ];
    return $bau;
  }

}
