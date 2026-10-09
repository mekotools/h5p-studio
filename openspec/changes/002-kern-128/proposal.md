# Change 002 — H5P-Kern 1.28 (neuere Inhalte aufnehmbar)

**Status:** In Arbeit
**Datum:** 09.10.2026
**Auslöser:** Nutzerauftrag „lade Beispiele runter und fülle unseren Katalog mit
20–30 Beispielen" (09.10.2026). Beim Einspielen scheiterte die Mehrheit der
ausgewählten Pakete an der Kernfassung.

## Why

Das Studio ist im Bau auf `h5p/h5p-core 1.27.0` festgenagelt. Der Grund steht im
Bauplan und ist zeitgebunden: Kern 1.28 hat seiner Schnittstelle
`H5PFrameworkInterface` die Methode `resetHubOrganizationData()` hinzugefügt; das
Drupal-Modul `drupal/h5p 2.0.0-beta1` bringt sie nicht mit und lässt sich damit
nicht mehr instanziieren.

Inzwischen ist das eine Sackgasse: **12 von 30** geprüften Beispielen aus dem
Themenkomplex Medienbildung verlangen `coreApi` 1.28 — und zwar gerade die
inhaltlich besten (Urheberrechte, Suchen und Finden im Internet, Soziale
Netzwerke, Bildersuche nach CC-Lizenz, Künstliche Intelligenz – Ethik,
Zitierregeln, Studienübersicht Mediennutzung). Sie werden mit der Fehlermeldung
„requires a newer version of the H5P plugin" abgewiesen. Der Trend gilt generell:
neu veröffentlichte OER entsteht auf 1.28.

Eine Prüfung des Modulzweigs `2.0.x` (Stand 09.10.2026) zeigt: die Methode fehlt
auch dort. Auf einen Nachtrag des Moduls zu warten heißt, den halben Katalog nicht
aufnehmen zu können.

## What Changes

- Die Studio-Instanz läuft mit **H5P-Kern 1.28.0** statt 1.27.0.
- Beobachtbare Folge: **H5P-Pakete, die `coreApi` 1.28 verlangen, lassen sich
  aufnehmen.** Bisher endete das mit der Meldung „requires a newer version of the
  H5P plugin. This site is currently running version 1.27".
- Die fehlende Schnittstellenmethode wird **im Bau** nachgetragen (Flickdatei auf
  das Modul, nicht in die laufende Installation hineingepfuscht). Sie leistet, was
  die Schnittstelle verlangt: verwirft die hinterlegte Seitenkennung, wenn der Hub
  die Zugangsdaten mit 403 abweist, damit sich die Seite neu anmelden kann. Für
  den Ablauf „Inhalte aufnehmen und abspielen" ist sie ohne Wirkung — sie wird nur
  auf den Hub-Kontowegen aufgerufen, die wir nicht benutzen.
- Der Bau prüft beides selbst: Kern 1.28 liegt im Abbild **und** die Methode ist
  im Modul vorhanden. Fehlt eines, bricht die Fertigung ab, statt ein kaputtes
  Abbild auszuliefern.
- Kein Inhaltsverlust, keine Datenwanderung: die vorhandene Datenbank (0 Inhalte)
  wird unverändert weiterverwendet; bestehende Bibliotheken bleiben liegen.

Nicht in diesem Change: eigene Anhebung auf spätere Kernfassungen, Beitragen der
Flickdatei an das Modul (sinnvoll, aber ein eigener Vorgang).

## Specs-Delta

- **ADDED** `betrieb` — Aufnahmefähigkeit als Betriebseigenschaft: welche
  Kernfassung läuft und was daraus für die Aufnahme folgt.

## Archivierungs-Hinweis

Change 001 (`betrieb`) ist noch nicht archiviert, es gibt also noch keine
Live-Spec `betrieb`. Deshalb ist das Delta als **ADDED** geschrieben und nicht als
MODIFIED. Beim Archivieren zuerst Change 001 anwenden, danach dieses.
