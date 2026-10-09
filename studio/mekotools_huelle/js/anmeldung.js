/**
 * @file
 * Anmeldung in einem Zug.
 *
 * Der Anmelden-Knopf der Kopfleiste führt auf Drupals Zwischenseite „Über
 * OpenID Connect anmelden". Dort steht genau ein Anbieter (Pocket ID), die
 * Seite wäre also ein zusätzlicher Halt in einer Umgebung, die keine Naht haben
 * soll. Steht genau ein Anmelde-Knopf zur Wahl, wird er deshalb selbst
 * ausgelöst — die Person landet unmittelbar bei Pocket ID.
 *
 * Ohne JavaScript bleibt die Seite stehen und alles funktioniert weiter: der
 * Knopf ist da, nur ein Klick mehr. Bei mehreren Anbietern geschieht nichts —
 * dann gehört die Auswahl der Person.
 */
(function () {
  'use strict';

  var formular = document.getElementById('openid-connect-login-form');
  if (!formular) {
    return;
  }

  var knoepfe = formular.querySelectorAll('input[type="submit"], button[type="submit"]');
  if (knoepfe.length !== 1) {
    return;
  }

  knoepfe[0].click();
})();
