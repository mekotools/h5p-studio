<?php

namespace Drupal\mekotools_studio\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mekotools_studio\Antragsspeicher;
use Drupal\mekotools_studio\PocketId;
use Drupal\mekotools_studio\Stufen;

/**
 * Bestätigt eine angemeldete Person als Fachkraft (Stufe 2).
 *
 * Stufe 1 heißt: angemeldet, aber nicht bestätigt. Erst diese Bestätigung
 * öffnet die Werkzeuge, die pädagogische Betreuung voraussetzen. Die untere
 * Stufe bleibt bestehen — die Stufen sind eine Leiter, kein Wechsel.
 */
class BestaetigungForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mekotools_studio_antrag_bestaetigen';
  }

  /**
   * Der Antrag, um den es geht.
   */
  protected function antrag(): ?object {
    $nummer = (int) \Drupal::routeMatch()->getParameter('nummer');
    return (new Antragsspeicher())->laden($nummer);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $antrag = $this->antrag();
    return $antrag
      ? $this->t('@name (@adresse) als Fachkraft bestätigen?', [
        '@name' => $antrag->name,
        '@adresse' => $antrag->adresse,
      ])
      : $this->t('Diesen Antrag gibt es nicht mehr.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $antrag = $this->antrag();
    $zusatz = '';
    if ($antrag && (string) $antrag->rolle === 'lehrkraft') {
      $zusatz = ' Die Person hat sich als Lehrkraft gemeldet.';
    }
    elseif ($antrag && (string) $antrag->rolle === 'fachkraft') {
      $zusatz = ' Die Person hat sich als Fachkraft gemeldet.';
    }
    return $this->t('Damit steigt @adresse auf Stufe 2 und erhält Zugang zu den Werkzeugen, '
      . 'die pädagogische Betreuung voraussetzen. Die Stufe „angemeldet" bleibt bestehen.'
      . $zusatz, ['@adresse' => ($antrag->adresse ?? '')]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Als Fachkraft bestätigen');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('mekotools_studio.antraege');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $speicher = new Antragsspeicher();
    $antrag = $this->antrag();
    if (!$antrag) {
      $this->messenger()->addError('Diesen Antrag gibt es nicht mehr.');
      $form_state->setRedirectUrl($this->getCancelUrl());
      return;
    }

    try {
      $pocket = new PocketId();
      $gruppe = $pocket->gruppe(Stufen::FACHKRAFT);
      if (!$gruppe) {
        throw new \RuntimeException('Im Anmeldedienst gibt es keine Gruppe „'
          . Stufen::FACHKRAFT . '". Bitte dort zuerst anlegen.');
      }
      $konto_id = (string) $antrag->konto;
      if ($konto_id === '') {
        $konto = $pocket->kontoNachAdresse($antrag->adresse);
        if (!$konto) {
          throw new \RuntimeException('Zu dieser Adresse gibt es kein Konto im Anmeldedienst. Bitte erst aufnehmen.');
        }
        $konto_id = $konto['id'];
      }
      $pocket->gruppeHinzufuegen($konto_id, $gruppe['id']);
      $speicher->zustandSetzen((int) $antrag->nummer, 'bestaetigt',
        'Stufe 2 (Fachkraft bestätigt).',
        (int) $this->currentUser()->id(), $konto_id);
      $this->messenger()->addStatus($this->t('@adresse ist jetzt als Fachkraft bestätigt (Stufe 2). Der Zugang gilt ab der nächsten Anmeldung.', [
        '@adresse' => $antrag->adresse,
      ]));
      $this->logger('mekotools_studio')->notice('Antrag @nummer bestätigt (Stufe 2), Konto @konto.', [
        '@nummer' => $antrag->nummer,
        '@konto' => $konto_id,
      ]);
    }
    catch (\Throwable $e) {
      $this->messenger()->addError($this->t('Die Bestätigung ist nicht durchgelaufen: @meldung', [
        '@meldung' => $e->getMessage(),
      ]));
      $this->logger('mekotools_studio')->error('Bestätigung von Antrag @nummer fehlgeschlagen: @meldung', [
        '@nummer' => $antrag->nummer,
        '@meldung' => $e->getMessage(),
      ]);
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
