<?php

namespace Drupal\mekotools_studio\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * /studio führt zur Bibliothek — kurze Adresse für den Alltag.
 */
class KurzwegController extends ControllerBase {

  /**
   * Leitet weiter.
   */
  public function weiter() {
    return $this->redirect('mekotools_studio.bibliothek');
  }

}
