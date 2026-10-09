<?php

namespace Drupal\mekotools_studio;

/**
 * Die Stufen der Berechtigung.
 *
 * Eine Leiter, kein Haufen loser Gruppen: jede Stufe schließt die
 * darunterliegenden ein. Werkzeuge fordern künftig eine MINDESTSTUFE an
 * („ab Fachkraft bestätigt") statt einer einzelnen Gruppe — dann lässt sich
 * später eine Stufe dazwischen schieben (Redakteur und so weiter), ohne alles
 * umzubauen. Die Gruppen leben im Anmeldedienst (Pocket ID), dort sind sie für
 * jedes Werkzeug sichtbar; hier im Studio hängt an jeder Stufe eine Rolle.
 *
 *   Stufe 1  angemeldet   Konto vorhanden, noch nicht bestätigt.
 *   Stufe 2  lehrkraefte  als Fachkraft/Lehrkraft bestätigt.
 *   Stufe 3  verwaltung   Verwaltung: sieht Anträge und gibt sie frei.
 */
class Stufen {

  /**
   * Stufe 1: angemeldet, aber nicht bestätigt.
   */
  const ANGEMELDET = 'angemeldet';

  /**
   * Stufe 2: bestätigte Fachkraft (Lehrkräfte eingeschlossen).
   */
  const FACHKRAFT = 'lehrkraefte';

  /**
   * Stufe 3: Verwaltung, darf Zugänge und Konten verwalten.
   */
  const VERWALTUNG = 'verwaltung';

  /**
   * Die Leiter von unten nach oben.
   *
   * @return array
   *   Gruppe => [Stufe, Rolle im Studio, Bezeichnung].
   */
  public static function leiter(): array {
    return [
      self::ANGEMELDET => [1, 'angemeldet', 'angemeldet'],
      self::FACHKRAFT => [2, 'lehrkraft', 'Fachkraft bestätigt'],
      self::VERWALTUNG => [3, 'verwaltung', 'Verwaltung'],
    ];
  }

  /**
   * Die Gruppe zu einer Stufennummer.
   */
  public static function gruppe(int $stufe): ?string {
    foreach (self::leiter() as $gruppe => $werte) {
      if ($werte[0] === $stufe) {
        return $gruppe;
      }
    }
    return NULL;
  }

  /**
   * Die Stufennummer einer Gruppe (0 = unbekannt).
   */
  public static function nummer(string $gruppe): int {
    return self::leiter()[$gruppe][0] ?? 0;
  }

  /**
   * Die Rolle im Studio zu einer Gruppe.
   */
  public static function rolle(string $gruppe): ?string {
    return self::leiter()[$gruppe][1] ?? NULL;
  }

  /**
   * Die lesbare Bezeichnung einer Gruppe.
   */
  public static function bezeichnung(string $gruppe): string {
    return self::leiter()[$gruppe][2] ?? $gruppe;
  }

}
