<?php

namespace Drupal\mekotools_studio\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mekotools_studio\Antragsspeicher;

/**
 * Lehnt einen Zugangsantrag ab — mit Grund, damit die Entscheidung
 * nachvollziehbar bleibt.
 */
class AblehnungForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mekotools_studio_antrag_ablehnen';
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
      ? $this->t('Antrag von @name (@adresse) ablehnen?', [
        '@name' => $antrag->name,
        '@adresse' => $antrag->adresse,
      ])
      : $this->t('Diesen Antrag gibt es nicht mehr.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Die Person bekommt keine Nachricht. Der Antrag bleibt mit dem Grund in der '
      . 'Liste „Zuletzt entschieden" stehen.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Ablehnen');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['grund'] = [
      '#type' => 'textfield',
      '#title' => 'Grund (nur für die Akten)',
      '#maxlength' => 200,
      '#description' => 'Zum Beispiel „kein Bezug zu Schule oder Bildung" oder „Adresse nicht zustellbar".',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $antrag = $this->antrag();
    if (!$antrag) {
      $this->messenger()->addError('Diesen Antrag gibt es nicht mehr.');
      $form_state->setRedirectUrl($this->getCancelUrl());
      return;
    }
    $grund = trim((string) $form_state->getValue('grund'));
    $vermerk = 'Abgelehnt' . ($grund !== '' ? ': ' . $grund : ' (ohne Grund angegeben)');
    (new Antragsspeicher())->zustandSetzen((int) $antrag->nummer, 'abgelehnt', $vermerk, (int) $this->currentUser()->id());
    $this->messenger()->addStatus($this->t('Antrag von @name abgelehnt.', ['@name' => $antrag->name]));
    $this->logger('mekotools_studio')->notice('Zugangsantrag @nummer abgelehnt (@grund).', [
      '@nummer' => $antrag->nummer,
      '@grund' => $grund,
    ]);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
