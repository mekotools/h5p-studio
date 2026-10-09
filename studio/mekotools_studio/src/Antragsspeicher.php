<?php

namespace Drupal\mekotools_studio;

/**
 * Speicher der Zugangsanträge.
 *
 * Bewusst eine eigene Tabelle und keine Inhaltsart: Anträge sind Vorgänge mit
 * Zustand (offen / eingeladen / abgelehnt), keine Inhalte, die im Studio
 * erscheinen sollen.
 */
class Antragsspeicher {

  /**
   * Name der Tabelle.
   */
  const TABELLE = 'mekotools_studio_zugangsantrag';

  /**
   * Mögliche Zustände eines Antrags.
   */
  const ZUSTAENDE = [
    'offen' => 'offen',
    'aufgenommen' => 'aufgenommen',
    'bestaetigt' => 'bestaetigt',
    'abgelehnt' => 'abgelehnt',
  ];

  /**
   * Legt einen Antrag an und gibt dessen Nummer zurück.
   */
  public function anlegen(array $felder): int {
    $felder += [
      'angelegt' => \Drupal::time()->getRequestTime(),
      'zustaendig' => 0,
      'vermerk' => '',
    ];
    return (int) $this->tabelle()->insert(self::TABELLE)
      ->fields($felder)
      ->execute();
  }

  /**
   * Alle Anträge, neueste zuerst.
   *
   * @param string|null $zustand
   *   Nur dieser Zustand; ohne Angabe alle.
   *
   * @return array
   *   Liste von Datensätzen.
   */
  public function alle(?string $zustand = NULL): array {
    $abfrage = $this->tabelle()->select(self::TABELLE, 'a')->orderBy('angelegt', 'DESC');
    $abfrage->fields('a');
    if ($zustand !== NULL) {
      $abfrage->condition('zustand', $zustand);
    }
    return $abfrage->execute()->fetchAll();
  }

  /**
   * Ein einzelner Antrag.
   */
  public function laden(int $nummer): ?object {
    $zeile = $this->tabelle()->select(self::TABELLE, 'a')
      ->fields('a')
      ->condition('nummer', $nummer)
      ->execute()
      ->fetchObject();
    return $zeile ?: NULL;
  }

  /**
   * Zählt Anträge in einem Zustand.
   */
  public function anzahl(string $zustand = 'offen'): int {
    return (int) $this->tabelle()->select(self::TABELLE, 'a')
      ->condition('zustand', $zustand)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Hat diese Adresse schon einen offenen Antrag?
   */
  public function offenFuer(string $adresse): bool {
    return (bool) $this->tabelle()->select(self::TABELLE, 'a')
      ->condition('adresse', $adresse)
      ->condition('zustand', 'offen')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Setzt den Zustand eines Antrags.
   *
   * @param string $zustand
   *   Einer der Werte aus self::ZUSTAENDE.
   * @param string $vermerk
   *   Was passiert ist (Stufe, Kennung im Anmeldedienst, Fehlermeldung, Grund).
   * @param string $konto
   *   Kennung des Kontos im Anmeldedienst, wenn es sie gibt.
   */
  public function zustandSetzen(int $nummer, string $zustand, string $vermerk = '', int $bearbeiter = 0, string $konto = ''): void {
    if (!isset(self::ZUSTAENDE[$zustand])) {
      throw new \InvalidArgumentException('Unbekannter Zustand: ' . $zustand);
    }
    $felder = [
      'zustand' => $zustand,
      'vermerk' => mb_substr($vermerk, 0, 500),
      'bearbeitet' => \Drupal::time()->getRequestTime(),
      'zustaendig' => $bearbeiter,
    ];
    if ($konto !== '') {
      $felder['konto'] = $konto;
    }
    $this->tabelle()->update(self::TABELLE)
      ->fields($felder)
      ->condition('nummer', $nummer)
      ->execute();
  }

  /**
   * Die Tabelle.
   */
  protected function tabelle() {
    return \Drupal::database();
  }

}
