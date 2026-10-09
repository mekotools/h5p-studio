<?php

namespace Drupal\mekotools_studio;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Zugang zum Anmeldedienst (Pocket ID).
 *
 * Das Studio legt hier Konten an und lässt die Einladung verschicken. Wer die
 * Einladung einlöst, legt selbst einen Passkey an — im Studio selbst entstehen
 * keine Kennwörter.
 *
 * Der Verwaltungsschlüssel kommt aus der Umgebung (STUDIO_POCKETID_SCHLUESSEL),
 * nie aus dem Quelltext.
 */
class PocketId {

  /**
   * Vorgabe für die Adresse des Anmeldedienstes.
   */
  const BASIS_VORGABE = 'https://auth.mekotools.de';

  /**
   * Gruppe, die im Studio die Rolle „Lehrkraft" bekommt.
   */
  const GRUPPE_LEHRKRAEFTE = 'lehrkraefte';

  /**
   * Wie lange eine Einladung gültig bleibt (Go-Schreibweise, Zeichenkette).
   */
  const EINLADUNG_GUELTIG = '168h';

  protected string $basis;
  protected string $schluessel;
  protected ClientInterface $klient;
  protected LoggerInterface $protokoll;

  /**
   * Baut den Zugang.
   *
   * @param \GuzzleHttp\ClientInterface|null $klient
   *   HTTP-Klient; ohne Angabe der des Studios.
   * @param \Psr\Log\LoggerInterface|null $protokoll
   *   Protokoll; ohne Angabe das des Moduls.
   */
  public function __construct(?ClientInterface $klient = NULL, ?LoggerInterface $protokoll = NULL) {
    $werte = (array) (\Drupal\Core\Site\Settings::get('mekotools_studio_pocketid') ?? []);
    if (!empty($werte['basis'])) {
      $this->basis = rtrim((string) $werte['basis'], '/');
    }
    else {
      $basis = getenv('STUDIO_POCKETID_BASIS') ?: self::BASIS_VORGABE;
      $this->basis = rtrim($basis, '/');
    }
    if (!empty($werte['schluessel'])) {
      $this->schluessel = (string) $werte['schluessel'];
    }
    else {
      $this->schluessel = (string) (getenv('STUDIO_POCKETID_SCHLUESSEL') ?: '');
    }
    $this->klient = $klient ?: \Drupal::httpClient();
    $this->protokoll = $protokoll ?: \Drupal::logger('mekotools_studio');
  }

  /**
   * Ist der Zugang eingerichtet?
   *
   * Ohne Schlüssel kann nichts angelegt werden — die Freigabeseite sagt das
   * dann offen, statt still zu scheitern.
   */
  public function bereit(): bool {
    return $this->schluessel !== '';
  }

  /**
   * Sucht eine Gruppe nach Namen.
   *
   * @return array|null
   *   Die Gruppe (mit „id") oder NULL.
   */
  public function gruppe(string $name): ?array {
    $treffer = $this->ruf('/api/user-groups', 'GET', ['search' => $name]);
    foreach (($treffer['data'] ?? []) as $gruppe) {
      if (($gruppe['name'] ?? '') === $name) {
        return $gruppe;
      }
    }
    return NULL;
  }

  /**
   * Sucht ein Konto nach E-Mail-Adresse.
   *
   * @return array|null
   *   Das Konto (mit „id") oder NULL.
   */
  public function kontoNachAdresse(string $adresse): ?array {
    $treffer = $this->ruf('/api/users', 'GET', ['search' => $adresse]);
    foreach (($treffer['data'] ?? []) as $konto) {
      if (strcasecmp((string) ($konto['email'] ?? ''), $adresse) === 0) {
        return $konto;
      }
    }
    return NULL;
  }

  /**
   * Legt ein Konto an.
   *
   * @param string $adresse
   *   E-Mail-Adresse; wird auch zur Kennung, solange sie frei ist.
   * @param string $name
   *   Vor- und Nachname in einem Stück.
   * @param string[] $gruppen_ids
   *   Kennungen der Gruppen, in die das Konto gehört.
   *
   * @return array
   *   Das angelegte Konto.
   *
   * @throws \RuntimeException
   *   Wenn der Anmeldedienst ablehnt.
   */
  public function kontoAnlegen(string $adresse, string $name, array $gruppen_ids = []): array {
    $teile = preg_split('/\s+/', trim($name), 2) ?: [];
    $vorname = $teile[0] ?? '';
    $nachname = $teile[1] ?? '';

    $koerper = [
      'email' => $adresse,
      'firstName' => $vorname,
      'lastName' => $nachname,
      'locale' => 'de',
      'userGroupIds' => array_values($gruppen_ids),
    ];

    // Die Kennung muss eindeutig sein; notfalls wird gezählt.
    $stamm = $this->kontoKennung($adresse);
    $letzter = NULL;
    for ($versuch = 0; $versuch < 6; $versuch++) {
      $koerper['username'] = $versuch === 0 ? $stamm : $stamm . $versuch;
      try {
        return $this->ruf('/api/users', 'POST', [], $koerper);
      }
      catch (RuntimeException $e) {
        $letzter = $e;
        // Nur bei belegter Kennung weiterzählen, sonst sofort melden.
        if (!str_contains(mb_strtolower($e->getMessage()), 'username')) {
          throw $e;
        }
      }
    }
    throw $letzter;
  }

  /**
   * Nimmt ein Konto in weitere Gruppen auf.
   *
   * @param string[] $gruppen_ids
   *   Vollständige Liste der Gruppen des Kontos.
   */
  public function gruppenSetzen(string $konto_id, array $gruppen_ids): void {
    $this->ruf('/api/users/' . $konto_id . '/user-groups', 'PUT', [], [
      'userGroupIds' => array_values($gruppen_ids),
    ]);
  }

  /**
   * Lässt den Anmeldedienst die Einladung verschicken.
   *
   * Der Anmeldedienst verschickt die Mail selbst (Brief „Login Code"); sie
   * enthält den Link, mit dem die Person ihren Passkey anlegt.
   */
  public function einladungSenden(string $konto_id, string $gueltig = self::EINLADUNG_GUELTIG): void {
    $this->ruf('/api/users/' . $konto_id . '/one-time-access-email', 'POST', [], [
      'ttl' => $gueltig,
      'redirectPath' => '/',
    ]);
  }

  /**
   * Leitet aus der Adresse eine Kennung ab (nur zulässige Zeichen).
   */
  protected function kontoKennung(string $adresse): string {
    $stamm = mb_strtolower(strstr($adresse, '@', TRUE) ?: $adresse);
    $stamm = preg_replace('/[^a-z0-9._-]+/', '.', $stamm) ?: 'konto';
    return trim($stamm, '._-') ?: 'konto';
  }

  /**
   * Ruft die Schnittstelle auf.
   *
   * @param array $abfrage
   *   Werte für die Adresszeile.
   * @param array|null $koerper
   *   Körper als Feld (wird als JSON gesendet).
   *
   * @return array
   *   Die Antwort als Feld.
   *
   * @throws \RuntimeException
   *   Bei Verbindungsfehlern oder wenn der Dienst ablehnt.
   */
  protected function ruf(string $pfad, string $methode, array $abfrage = [], ?array $koerper = NULL): array {
    if (!$this->bereit()) {
      throw new RuntimeException('Der Zugang zum Anmeldedienst ist nicht eingerichtet (STUDIO_POCKETID_SCHLUESSEL fehlt).');
    }
    $kopf = [
      'X-API-KEY' => $this->schluessel,
      'Accept' => 'application/json',
    ];
    $werte = ['headers' => $kopf];
    if ($abfrage) {
      $werte['query'] = $abfrage;
    }
    if ($koerper !== NULL) {
      $werte['json'] = $koerper;
    }
    try {
      $antwort = $this->klient->request($methode, $this->basis . $pfad, $werte);
    }
    catch (GuzzleException $e) {
      throw new RuntimeException('Der Anmeldedienst antwortet nicht: ' . $e->getMessage(), 0, $e);
    }
    $roh = (string) $antwort->getBody();
    $daten = $roh === '' ? [] : (Json::decode($roh) ?: []);
    if ($antwort->getStatusCode() >= 400) {
      $meldung = $daten['error'] ?? $daten['message'] ?? $roh;
      throw new RuntimeException('Der Anmeldedienst hat abgelehnt (' . $antwort->getStatusCode() . '): ' . $meldung);
    }
    return is_array($daten) ? $daten : [];
  }

}
