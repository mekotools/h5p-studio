<?php

namespace Drupal\mekotools_studio\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mekotools_studio\Antragsspeicher;
use Drupal\mekotools_studio\PocketId;
use Drupal\mekotools_studio\Stufen;

/**
 * Bestätigt und vollzieht die Freigabe eines Zugangsantrags.
 *
 * Hier entsteht das Konto im Anmeldedienst, hier wird die Einladung
 * verschickt. Scheitert etwas, steht die Meldung des Anmeldedienstes auf
 * dieser Seite — die Bestätigung schließt den Antrag nur bei Erfolg ab.
 */
class FreigabeForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mekotools_studio_antrag_freigeben';
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
      ? $this->t('Für @name (@adresse) ein Konto anlegen und die Einladung verschicken?', [
        '@name' => $antrag->name,
        '@adresse' => $antrag->adresse,
      ])
      : $this->t('Diesen Antrag gibt es nicht mehr.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Im Anmeldedienst entsteht ein Konto für @adresse. Die Person bekommt eine E-Mail '
      . 'und legt damit selbst einen Passkey an. Ein Kennwort wird nirgends vergeben.', [
        '@adresse' => ($this->antrag()->adresse ?? ''),
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Konto anlegen und Einladung senden');
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
      $gruppe = $pocket->gruppe(Stufen::ANGEMELDET);
      if (!$gruppe) {
        throw new \RuntimeException('Im Anmeldedienst gibt es keine Gruppe „'
          . Stufen::ANGEMELDET . '". Bitte dort zuerst anlegen.');
      }
      $konto = $pocket->kontoNachAdresse($antrag->adresse);
      if ($konto) {
        $konto_id = $konto['id'];
        // Hinzufügen, nicht ersetzen: wer schon höhere Stufen hat, verliert sie nicht.
        $pocket->gruppeHinzufuegen($konto_id, $gruppe['id']);
        $this->messenger()->addStatus($this->t('@adresse hat schon ein Konto; es wurde in die Stufe „angemeldet" aufgenommen.', [
          '@adresse' => $antrag->adresse,
        ]));
      }
      else {
        $konto = $pocket->kontoAnlegen($antrag->adresse, $antrag->name, [$gruppe['id']]);
        $konto_id = (string) ($konto['id'] ?? '');
        $this->messenger()->addStatus($this->t('Konto für @adresse angelegt.', ['@adresse' => $antrag->adresse]));
      }
      $pocket->einladungSenden($konto_id);
      $speicher->zustandSetzen((int) $antrag->nummer, 'aufgenommen',
        'Stufe 1 (angemeldet): Einladung verschickt.',
        (int) $this->currentUser()->id(), (string) $konto_id);
      $this->messenger()->addStatus($this->t('Die Einladung an @adresse ist unterwegs. Die Person ist jetzt angemeldet, aber noch nicht bestätigt.', [
        '@adresse' => $antrag->adresse,
      ]));
      $this->logger('mekotools_studio')->notice('Zugangsantrag @nummer aufgenommen (Stufe 1), Konto @konto.', [
        '@nummer' => $antrag->nummer,
        '@konto' => $konto_id,
      ]);
    }
    catch (\Throwable $e) {
      // Nichts scheitert still: die Person bleibt „offen", die Meldung steht hier.
      $this->messenger()->addError($this->t('Die Freigabe ist nicht durchgelaufen: @meldung', [
        '@meldung' => $e->getMessage(),
      ]));
      $this->logger('mekotools_studio')->error('Freigabe von Antrag @nummer fehlgeschlagen: @meldung', [
        '@nummer' => $antrag->nummer,
        '@meldung' => $e->getMessage(),
      ]);
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
