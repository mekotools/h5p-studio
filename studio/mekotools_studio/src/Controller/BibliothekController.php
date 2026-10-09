<?php

namespace Drupal\mekotools_studio\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;

/**
 * Die Bibliothek: alles, was im Haus an H5P-Inhalten liegt.
 *
 * Bewusst ohne Ansichten-Baukasten (Views): die Seite ist klein, die
 * Beschriftungen sind fest deutsch, und es gibt keine Konfiguration, die man
 * nicht im Quelltext nachlesen könnte.
 */
class BibliothekController extends ControllerBase {

  /**
   * Baut die Seite.
   */
  public function seite(Request $anfrage) {
    $suche = trim((string) $anfrage->query->get('s', ''));
    $fach = (string) $anfrage->query->get('fach', '');

    $abfrage = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'h5p_inhalt')
      ->condition('status', 1)
      ->sort('changed', 'DESC');

    if ($suche !== '') {
      $abfrage->condition('title', $suche, 'CONTAINS');
    }
    if ($fach !== '' && ctype_digit($fach)) {
      $abfrage->condition('field_fach', (int) $fach);
    }

    // Die Gesamtzahl zählen, BEVOR die Blätterung gesetzt ist. `execute()`
    // liefert sonst nur die Kennungen der aktuellen Seite: die Anzeige meldete
    // „25 Inhalte", obwohl 30 im Haus liegen (am 09.10.2026 gesehen), und auf
    // der zweiten Seite „5 Inhalte". Eine Kopie half nicht — die Blätterung
    // hängt an der Abfrage und wird bei jeder Ausführung angewendet.
    $gesamt = (int) (clone $abfrage)->count()->execute();
    $abfrage->pager(25);
    $kennungen = $abfrage->execute();
    $knoten = $kennungen ? Node::loadMultiple($kennungen) : [];

    // Auswahlliste der Fächer: nur, was wirklich vorhanden ist.
    $faecher = $this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'faecher']);
    $optionen = [];
    foreach ($faecher as $begriff) {
      $optionen[$begriff->id()] = $begriff->label();
    }
    $belegt = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'h5p_inhalt')
      ->condition('status', 1)
      ->condition('field_fach', array_keys($optionen), 'IN')
      ->execute();
    $zaehler = [];
    foreach (Node::loadMultiple($belegt) as $eintrag) {
      foreach ($eintrag->get('field_fach')->getValue() as $wert) {
        $zaehler[$wert['target_id']] = ($zaehler[$wert['target_id']] ?? 0) + 1;
      }
    }

    $zeilen = [];
    foreach ($knoten as $eintrag) {
      $zeilen[] = [
        ['data' => ['#markup' => '<strong>' . Html::escape($eintrag->label()) . '</strong>']],
        ['data' => ['#markup' => Html::escape($this->fach_name($eintrag))]],
        ['data' => ['#markup' => Html::escape($this->schlagworte($eintrag))]],
        ['data' => ['#markup' => Html::escape($this->kurz($eintrag))]],
        ['data' => ['#markup' => '<a href="' . Html::escape($this->adresse($eintrag)) . '">Abspielen</a>']],
      ];
    }

    $inhalt = [
      '#cache' => ['tags' => ['node_list:h5p_inhalt'], 'contexts' => ['url.query_args']],
      'kopf' => ['#markup' => '<p>Hier liegen alle H5P-Inhalte des Hauses. Jeder Inhalt hat eine Adresse, die ohne Anmeldung funktioniert — gut für den Klassenraum.</p>'],
      'formular' => ['#markup' => Markup::create($this->formular($suche, $fach, $optionen, $zaehler))],
      'anzahl' => ['#markup' => '<p><strong>' . ($gesamt === 1 ? '1 Inhalt' : $gesamt . ' Inhalte') . '</strong>'
        . ($suche !== '' ? ' zum Suchwort „' . Html::escape($suche) . '"' : '')
        . ($fach !== '' && isset($optionen[$fach]) ? ' im Fach ' . Html::escape($optionen[$fach]) : '') . '.</p>'],
    ];

    if ($zeilen) {
      $inhalt['tafel'] = [
        '#type' => 'table',
        '#header' => ['Titel', 'Fach', 'Schlagworte', 'Zuletzt geändert', ''],
        '#rows' => $zeilen,
        '#attributes' => ['class' => ['h5p-studio-bibliothek']],
      ];
      $inhalt['pager'] = ['#type' => 'pager'];
    }
    else {
      $inhalt['leer'] = ['#markup' => '<p><strong>Kein Inhalt gefunden.</strong> Suchwort oder Fach ändern, oder oben einen neuen Inhalt anlegen.</p>'];
    }

    if ($this->currentUser()->hasPermission('create h5p_inhalt content')) {
      $inhalt['neu'] = [
        '#markup' => '<p><a class="button" href="'
          . Html::escape(Url::fromRoute('node.add', ['node_type' => 'h5p_inhalt'])->toString())
          . '">Neuen Inhalt anlegen</a></p>',
        '#weight' => -10,
      ];
    }

    return $inhalt;
  }

  /**
   * Das Suchformular — echtes GET, damit die Adresse teilbar bleibt.
   */
  protected function formular(string $suche, string $fach, array $optionen, array $zaehler) {
    $html = '<form method="get" action="' . Html::escape(Url::fromRoute('mekotools_studio.bibliothek')->toString()) . '" class="h5p-studio-suche">';
    $html .= '<label for="h5p-studio-s">Suche</label> ';
    $html .= '<input id="h5p-studio-s" type="search" name="s" value="' . Html::escape($suche) . '" placeholder="Titel durchsuchen"> ';
    $html .= '<label for="h5p-studio-fach">Fach</label> ';
    $html .= '<select id="h5p-studio-fach" name="fach"><option value="">alle Fächer</option>';
    foreach ($optionen as $kennung => $name) {
      $zahl = $zaehler[$kennung] ?? 0;
      if ($zahl === 0) {
        continue;
      }
      $gewaehlt = ((string) $kennung === $fach) ? ' selected' : '';
      $html .= '<option value="' . Html::escape((string) $kennung) . '"' . $gewaehlt . '>'
        . Html::escape($name) . ' (' . $zahl . ')</option>';
    }
    $html .= '</select> <button type="submit">Anzeigen</button>';
    $html .= '</form>';
    return $html;
  }

  /**
   * Fach eines Inhalts als Text.
   */
  protected function fach_name(Node $knoten) {
    $werte = $knoten->get('field_fach')->getValue();
    if (!$werte) {
      return '—';
    }
    $begriff = Term::load($werte[0]['target_id']);
    return $begriff ? $begriff->label() : '—';
  }

  /**
   * Schlagworte eines Inhalts als Text.
   */
  protected function schlagworte(Node $knoten) {
    $namen = [];
    foreach ($knoten->get('field_schlagworte')->getValue() as $wert) {
      $begriff = Term::load($wert['target_id']);
      if ($begriff) {
        $namen[] = $begriff->label();
      }
    }
    return $namen ? implode(', ', $namen) : '—';
  }

  /**
   * Kurzbeschreibung (gekürzt) — hilft beim Wiederfinden.
   */
  protected function kurz(Node $knoten) {
    if (!$knoten->hasField('body') || $knoten->get('body')->isEmpty()) {
      return \Drupal::service('date.formatter')->format($knoten->getChangedTime(), 'short');
    }
    $text = trim(strip_tags((string) $knoten->get('body')->value));
    $text = preg_replace('/\s+/u', ' ', $text);
    return \Drupal::service('date.formatter')->format($knoten->getChangedTime(), 'short') . ' · ' . mb_substr($text, 0, 90);
  }

  /**
   * Die Adresse zum Teilen (ohne Anmeldung abspielbar).
   */
  protected function adresse(Node $knoten) {
    return Url::fromRoute('entity.node.canonical', ['node' => $knoten->id()], ['absolute' => TRUE])->toString();
  }

}
