<?php

namespace Drupal\mekotools_studio\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mekotools_studio\Antragsspeicher;

/**
 * Öffentlicher Antrag auf Zugang zu MekoTools.
 *
 * Der Zugang gilt für alle Werkzeuge des Hauses, nicht für das Studio allein —
 * deshalb steht hier auch MekoTools und nicht der Name eines Werkzeugs. Die
 * Seite liegt nur technisch im Studio (Drupal kann ein Formular entgegennehmen,
 * die Hauptseite ist statisch); sie ist die einzige Antragsstrecke.
 *
 * Diese Seite ist ohne Anmeldung erreichbar — wer noch keinen Zugang hat, kann
 * sich hier melden. Der Antrag landet in einer Liste, die die Verwaltung
 * freigibt; erst die Freigabe legt ein Konto im Anmeldedienst an.
 */
class ZugangsantragForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mekotools_studio_zugangsantrag';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['hinweis'] = [
      '#markup' => '<p>Der <strong>Grundzugang</strong> zu MekoTools ist kostenlos und braucht keinen '
      . 'Antrag: Konto anlegen unter <a href="https://auth.mekotools.de/signup">auth.mekotools.de</a> — '
      . 'damit lassen sich Werkzeuge wie das H5P-Studio, Claper und Fuiz nutzen.</p>'
      . '<p>Dieser Antrag ist für den <strong>bestätigten Zugang (Lehrkraft)</strong>. Damit öffnen sich '
      . 'die weitergehenden Werkzeuge — etwa Shadowbroker. Wir prüfen den Antrag und schicken dir '
      . 'eine Einladung per E-Mail. Mit dieser Einladung richtest du deinen Zugang ein; die Anmeldung '
      . 'läuft über einen Passkey, ein Kennwort brauchst du nicht.</p>',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => 'Vor- und Nachname',
      '#required' => TRUE,
      '#maxlength' => 120,
    ];
    $form['adresse'] = [
      '#type' => 'email',
      '#title' => 'E-Mail-Adresse',
      '#required' => TRUE,
      '#maxlength' => 180,
      '#description' => 'An diese Adresse geht später die Einladung.',
    ];
    $form['einrichtung'] = [
      '#type' => 'textfield',
      '#title' => 'Schule oder Einrichtung',
      '#required' => TRUE,
      '#maxlength' => 160,
      '#description' => 'Zum Beispiel „Grundschule am Park" oder „Fortbildungsinstitut".',
    ];
    $form['rolle'] = [
      '#type' => 'select',
      '#title' => 'Ich bin …',
      '#required' => TRUE,
      '#options' => [
        'lehrkraft' => 'Lehrkraft',
        'fachkraft' => 'Fachkraft (Fortbildung, Beratung, Hochschule, Verwaltung)',
        'sonstiges' => 'etwas anderes',
      ],
    ];
    $form['begruendung'] = [
      '#type' => 'textarea',
      '#title' => 'Wofür brauchst du den Zugang?',
      '#required' => TRUE,
      '#rows' => 5,
      '#maxlength' => 1000,
      '#description' => 'Ein oder zwei Sätze genügen.',
    ];
    $form['einwilligung'] = [
      '#type' => 'checkbox',
      '#title' => 'Ich bin damit einverstanden, dass meine Angaben zur Prüfung des Antrags gespeichert werden.',
      '#required' => TRUE,
    ];

    $form['absenden'] = [
      '#type' => 'submit',
      '#value' => 'Antrag abschicken',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $adresse = trim((string) $form_state->getValue('adresse'));
    if (!\Drupal::service('email.validator')->isValid($adresse)) {
      $form_state->setErrorByName('adresse', 'Diese E-Mail-Adresse sieht nicht richtig aus. Bitte prüfe sie.');
    }
    $speicher = new Antragsspeicher();
    if ($speicher->offenFuer($adresse)) {
      $form_state->setErrorByName('adresse', 'Für diese Adresse liegt schon ein offener Antrag vor. Wir melden uns per E-Mail.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $speicher = new Antragsspeicher();
    $nummer = $speicher->anlegen([
      'name' => (string) $form_state->getValue('name'),
      'adresse' => (string) $form_state->getValue('adresse'),
      'einrichtung' => (string) $form_state->getValue('einrichtung'),
      'rolle' => (string) $form_state->getValue('rolle'),
      'begruendung' => (string) $form_state->getValue('begruendung'),
      'zustand' => 'offen',
    ]);
    \Drupal::logger('mekotools_studio')->notice('Zugangsantrag @nummer eingegangen (@adresse).', [
      '@nummer' => $nummer,
      '@adresse' => $form_state->getValue('adresse'),
    ]);
    $form_state->setRedirect('mekotools_studio.zugang_erhalten', [], ['query' => ['nummer' => $nummer]]);
  }

}
