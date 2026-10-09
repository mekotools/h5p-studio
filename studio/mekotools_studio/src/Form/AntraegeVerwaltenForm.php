<?php

namespace Drupal\mekotools_studio\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mekotools_studio\Antragsspeicher;
use Drupal\mekotools_studio\PocketId;

/**
 * Freigabeseite: Anträge ansehen, Zugang anlegen, Einladung verschicken.
 *
 * Diese Seite entscheidet, wer ins Studio kommt. Sie legt das Konto im
 * Anmeldedienst (Pocket ID) an, nimmt es in die Gruppe „lehrkraefte" auf und
 * lässt die Einladung verschicken. Schlägt etwas fehl, steht die Meldung des
 * Anmeldedienstes hier — nichts scheitert still.
 */
class AntraegeVerwaltenForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mekotools_studio_antraege_verwalten';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $speicher = new Antragsspeicher();
    $pocket = new PocketId();
    $offen = $speicher->anzahl('offen');

    $form['lage'] = [
      '#markup' => '<p><strong>' . ($offen === 1 ? 'Ein offener Antrag' : $offen . ' offene Anträge')
      . '</strong>. Wer hier freigegeben wird, erhält eine E-Mail vom Anmeldedienst und legt damit '
      . 'selbst einen Passkey an.</p>',
    ];
    if (!$pocket->bereit()) {
      $form['warnung'] = [
        '#markup' => '<p class="messages messages--warning">Der Zugang zum Anmeldedienst ist nicht '
        . 'eingerichtet: es fehlt <code>STUDIO_POCKETID_SCHLUESSEL</code> in der Umgebung des Studios. '
        . 'Ohne ihn lässt sich kein Konto anlegen.</p>',
      ];
    }

    if ($offen === 0) {
      $form['leer'] = ['#markup' => '<p>Zurzeit liegt kein offener Antrag vor.</p>'];
    }
    else {
      $kopf = ['Name', 'E-Mail-Adresse', 'Einrichtung', 'Ich bin', 'Eingegangen', 'Begründung', 'Entscheidung'];
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
              'einladen' => [
                '#type' => 'submit',
                '#name' => 'einladen_' . $antrag->nummer,
                '#value' => 'Zugang anlegen und Einladung senden',
                '#button_type' => 'primary',
                '#attributes' => ['title' => 'Legt das Konto im Anmeldedienst an und schickt die Einladung.'],
              ],
              'ablehnen' => [
                '#type' => 'submit',
                '#name' => 'ablehnen_' . $antrag->nummer,
                '#value' => 'Ablehnen',
                '#submit' => ['::ablehnen'],
                '#limit_validation_errors' => [],
              ],
            ],
          ],
        ];
      }
      $form['antraege'] = [
        '#type' => 'table',
        '#header' => $kopf,
        '#rows' => $zeilen,
        '#empty' => 'Keine offenen Anträge.',
        '#attributes' => ['class' => ['mekotools-studio-antraege']],
      ];
    }

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
      $form['verlauf_titel'] = ['#markup' => '<h3>Zuletzt entschieden</h3>'];
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
      $form['verlauf'] = [
        '#type' => 'table',
        '#header' => ['Name', 'E-Mail-Adresse', 'Zustand', 'Entschieden am', 'Vermerk'],
        '#rows' => $zeilen,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ausloeser = (string) $form_state->getTriggeringElement()['#name'];
    if (!preg_match('/^(einladen|ablehnen)_(\d+)$/', $ausloeser, $teile)) {
      return;
    }
    $tat = $teile[1];
    $nummer = (int) $teile[2];
    $speicher = new Antragsspeicher();
    $antrag = $speicher->laden($nummer);
    if (!$antrag) {
      $this->messenger()->addError('Diesen Antrag gibt es nicht mehr.');
      return;
    }
    if ($tat === 'ablehnen') {
      $speicher->zustandSetzen($nummer, 'abgelehnt', 'Abgelehnt über die Freigabeseite.', (int) $this->currentUser()->id());
      $this->messenger()->addStatus(sprintf('Antrag von %s abgelehnt.', $antrag->name));
      \Drupal::logger('mekotools_studio')->notice('Zugangsantrag @nummer abgelehnt.', ['@nummer' => $nummer]);
      return;
    }

    try {
      $pocket = new PocketId();
      $gruppe = $pocket->gruppe(PocketId::GRUPPE_LEHRKRAEFTE);
      if (!$gruppe) {
        throw new \RuntimeException('Im Anmeldedienst gibt es keine Gruppe „' . PocketId::GRUPPE_LEHRKRAEFTE . '". Bitte zuerst dort anlegen.');
      }
      $konto = $pocket->kontoNachAdresse($antrag->adresse);
      if ($konto) {
        // Schon vorhanden: nur in die Gruppe aufnehmen.
        $konto_id = $konto['id'];
        $pocket->gruppenSetzen($konto_id, [$gruppe['id']]);
        $this->messenger()->addStatus(sprintf('%s hat schon ein Konto; es wurde in die Gruppe aufgenommen.', $antrag->adresse));
      }
      else {
        $konto = $pocket->kontoAnlegen($antrag->adresse, $antrag->name, [$gruppe['id']]);
        $konto_id = $konto['id'] ?? '';
        $this->messenger()->addStatus(sprintf('Konto für %s angelegt.', $antrag->adresse));
      }
      $pocket->einladungSenden($konto_id);
      $speicher->zustandSetzen($nummer, 'eingeladen', 'Konto ' . $konto_id . ' im Anmeldedienst; Einladung verschickt.', (int) $this->currentUser()->id());
      $this->messenger()->addStatus(sprintf('Einladung an %s ist unterwegs.', $antrag->adresse));
      \Drupal::logger('mekotools_studio')->notice('Zugangsantrag @nummer freigegeben, Konto @konto.', [
        '@nummer' => $nummer,
        '@konto' => $konto_id,
      ]);
    }
    catch (\Throwable $e) {
      $this->messenger()->addError('Die Freigabe ist nicht durchgelaufen: ' . $e->getMessage());
      \Drupal::logger('mekotools_studio')->error('Freigabe von Antrag @nummer fehlgeschlagen: @meldung', [
        '@nummer' => $nummer,
        '@meldung' => $e->getMessage(),
      ]);
    }
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
