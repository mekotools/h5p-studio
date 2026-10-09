<?php

namespace Drupal\mekotools_studio\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\mekotools_studio\Antragsspeicher;
use Drupal\mekotools_studio\PocketId;

/**
 * Freigabeseite: zeigt die Zugangsanträge und die Wege zur Entscheidung.
 *
 * Bewusst eine reine Anzeigeseite mit Verweisen: das Anlegen eines Kontos und
 * das Verschicken der Einladung sind folgenreich und bekommen deshalb je eine
 * eigene Bestätigungsseite (Klasse FreigabeForm / AblehnungForm).
 */
class AntraegeController extends ControllerBase {

  /**
   * Baut die Übersicht.
   */
  public function seite() {
    $speicher = new Antragsspeicher();
    $pocket = new PocketId();
    $offen = $speicher->anzahl('offen');

    $bau = [];
    $bau['lage'] = [
      '#markup' => '<p><strong>' . ($offen === 1 ? 'Ein offener Antrag' : $offen . ' offene Anträge')
      . '</strong>. Wer freigegeben wird, bekommt eine E-Mail vom Anmeldedienst und legt damit selbst '
      . 'einen Passkey an.</p>',
    ];
    if (!$pocket->bereit()) {
      $bau['warnung'] = [
        '#markup' => '<p class="messages messages--warning">Der Zugang zum Anmeldedienst ist nicht '
        . 'eingerichtet: <code>STUDIO_POCKETID_SCHLUESSEL</code> fehlt in der Umgebung des Studios. '
        . 'Ohne ihn lässt sich kein Konto anlegen.</p>',
      ];
    }

    $zeilen = [];
    foreach ($speicher->alle('offen') as $antrag) {
      $zeilen[] = [
        ['data' => ['#plain_text' => $antrag->name]],
        ['data' => ['#plain_text' => $antrag->adresse]],
        ['data' => ['#plain_text' => $antrag->einrichtung]],
        ['data' => ['#plain_text' => $this->rolleLesbar($antrag->rolle)]],
        ['data' => ['#plain_text' => \Drupal::service('date.formatter')->format((int) $antrag->angelegt, 'short')]],
        ['data' => ['#plain_text' => $antrag->begruendung]],
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'freigeben' => [
                'title' => 'Zugang anlegen und Einladung senden',
                'url' => Url::fromRoute('mekotools_studio.antrag_freigeben', ['nummer' => $antrag->nummer]),
              ],
              'ablehnen' => [
                'title' => 'Ablehnen',
                'url' => Url::fromRoute('mekotools_studio.antrag_ablehnen', ['nummer' => $antrag->nummer]),
              ],
            ],
          ],
        ],
      ];
    }
    $bau['antraege'] = [
      '#type' => 'table',
      '#header' => ['Name', 'E-Mail-Adresse', 'Einrichtung', 'Ich bin', 'Eingegangen', 'Begründung', 'Entscheidung'],
      '#rows' => $zeilen,
      '#empty' => 'Zurzeit liegt kein offener Antrag vor.',
      '#attributes' => ['class' => ['mekotools-studio-antraege']],
    ];

    $bearbeitet = [];
    foreach ($speicher->alle() as $antrag) {
      if ($antrag->zustand === 'offen') {
        continue;
      }
      $bearbeitet[] = $antrag;
      if (count($bearbeitet) >= 20) {
        break;
      }
    }
    if ($bearbeitet) {
      $zeilen = [];
      foreach ($bearbeitet as $antrag) {
        $zeilen[] = [
          ['data' => ['#plain_text' => $antrag->name]],
          ['data' => ['#plain_text' => $antrag->adresse]],
          ['data' => ['#plain_text' => $antrag->zustand === 'eingeladen' ? 'eingeladen' : 'abgelehnt']],
          ['data' => ['#plain_text' => $antrag->bearbeitet ? \Drupal::service('date.formatter')->format((int) $antrag->bearbeitet, 'short') : '']],
          ['data' => ['#plain_text' => $antrag->vermerk]],
        ];
      }
      $bau['verlauf_titel'] = ['#markup' => '<h3>Zuletzt entschieden</h3>'];
      $bau['verlauf'] = [
        '#type' => 'table',
        '#header' => ['Name', 'E-Mail-Adresse', 'Zustand', 'Entschieden am', 'Vermerk'],
        '#rows' => $zeilen,
      ];
    }

    // Diese Seite zeigt den Stand der Anträge. Zwischengespeichert wäre sie nach
    // einer Entscheidung falsch — genau das passierte am 09.10.2026: nach
    // Freigabe und Ablehnung standen weiterhin „1 offener Antrag" und kein
    // Verlauf auf der Seite. Deshalb wird sie bei jedem Aufruf neu gebaut.
    $bau['#cache'] = ['max-age' => 0];

    return $bau;
  }

  /**
   * Macht aus dem Kennwert der Rolle eine lesbare Bezeichnung.
   */
  protected function rolleLesbar(string $rolle): string {
    return [
      'lehrkraft' => 'Lehrkraft',
      'fachkraft' => 'Fachkraft',
      'sonstiges' => 'Sonstiges',
    ][$rolle] ?? $rolle;
  }

}
