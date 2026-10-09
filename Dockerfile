# MekoTools — H5P-Studio (Drupal 11 mit dem amtlichen H5P-Modul)
#
# Gebaut wird ausschliesslich in der eigenen Fertigung (CI, Läufer auf flip) —
# nie auf dem Zielrechner und nie im Agent-Behälter.
#
# Aufbau, bewusst:
#  * Grundlage ist das amtliche Drupal-Abbild. Es bringt den vollständigen
#    Drupal-Rumpf, PHP 8.4 mit den nötigen Erweiterungen (gd, pdo_mysql, zip),
#    Apache mit umgeschriebenen Adressen und composer mit.
#  * Stufe „bau" holt nur die Zusatzbausteine hinein (H5P, OIDC-Anmeldung, drush)
#    und schreibt dabei das Schloss (composer.lock) im Abbild fest.
#  * Die Auslieferstufe nimmt aus der Baustufe nur diese Zusätze mit — der
#    Drupal-Rumpf wird NICHT ein zweites Mal in das Abbild gelegt.
FROM drupal:11.4.8-apache AS bau

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /opt/drupal

# Feste Fassungen: H5P-Modul 2.0.0-beta1 (24.09.2025) ist die einzige Reihe für
# Drupal 10.2/11; openid_connect 3.0.0-alpha9 trägt Drupal 10.2/11.
#
# h5p/h5p-core steht auf 1.28.0. 1.28 hat der Schnittstelle die Methode
# resetHubOrganizationData() hinzugefügt, die das Drupal-Modul 2.0.0-beta1
# (und auch sein Zweig 2.0.x, geprüft am 09.10.2026) nicht mitbringt. Ohne
# Nachtrag bricht das Einschalten ab:
#   "H5PDrupal contains 1 abstract method and must therefore be declared
#    abstract or implement the remaining method
#    (H5PFrameworkInterface::resetHubOrganizationData)"
# 1.28 ist trotzdem nötig: neuer veröffentlichtes OER (u. a. aus dem H5P OER
# Hub) verlangt coreApi 1.28 — 12 von 30 geprüften Beispielen wurden unter
# 1.27 abgewiesen ("requires a newer version of the H5P plugin").
# Der Nachtrag steht als Flickdatei unter docker/flickdateien/ und wird hier
# angewendet; der Bau prüft anschliessend, dass er wirklich sitzt. Sobald das
# Modul nachzieht, fällt beides weg (Flickdatei und Prüfzeilen).
COPY docker/flickdateien/ /tmp/flickdateien/
RUN composer require --no-interaction --no-progress --with-all-dependencies \
      "drupal/h5p:2.0.0-beta1" \
      "h5p/h5p-core:1.28.0" \
      "drupal/openid_connect:3.0.0-alpha9" \
      "drush/drush:^13.6" \
 && composer check-platform-reqs \
 && test -f web/modules/contrib/h5p/h5p.info.yml \
 && test -f web/modules/contrib/h5p/modules/h5peditor/h5peditor.info.yml \
 && test -f web/modules/contrib/openid_connect/openid_connect.info.yml \
 && grep -q resetHubOrganizationData vendor/h5p/h5p-core/h5p.classes.php \
 && patch -p1 --forward < /tmp/flickdateien/h5p-resethuborganizationdata.patch \
 && grep -q resetHubOrganizationData web/modules/contrib/h5p/src/H5PDrupal/H5PDrupal.php \
 && php -l web/modules/contrib/h5p/src/H5PDrupal/H5PDrupal.php \
 && echo "H5P (Kern 1.28.0, Nachtrag sitzt), H5P-Editor und OIDC-Anmeldung liegen im Abbild"

# ---------------------------------------------------------------------------
FROM drupal:11.4.8-apache

# Der Aufkleber verknüpft das Paket in GHCR mit dem öffentlichen Spiegel-Repo —
# erst dadurch lässt es sich öffentlich stellen (Hausregel).
LABEL org.opencontainers.image.source="https://github.com/mekotools/h5p-studio" \
      org.opencontainers.image.title="H5P-Studio" \
      org.opencontainers.image.description="H5P-Inhalte erstellen, sammeln und abspielen (MekoTools)"

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    STUDIO_SEITE=/opt/drupal/web \
    STUDIO_DATEN=/daten

COPY --from=bau /opt/drupal/composer.json /opt/drupal/composer.lock /opt/drupal/
COPY --from=bau /opt/drupal/vendor/ /opt/drupal/vendor/
COPY --from=bau /opt/drupal/web/modules/contrib/ /opt/drupal/web/modules/contrib/

# Unsere eigene Konfiguration: Inhaltsart, H5P-Feld, Bibliotheksseite, deutsche Beschriftungen.
COPY studio/mekotools_studio/ /opt/drupal/web/modules/custom/mekotools_studio/
# Die Hülle des Hauses: eigenes Gerüst auf der Grundlage `stark`, Gestalt von
# mekotools.de (MkDocs Material, Hausfarbe #009485). Sie ersetzt Olivero an der
# Oberfläche; für die Verwaltung bleibt Claro zuständig.
COPY studio/mekotools_huelle/ /opt/drupal/web/themes/custom/mekotools_huelle/
COPY docker/eintritt.sh /usr/local/bin/eintritt.sh

# Der Dateibereich liegt in einem Datenträger, nicht im Abbild: H5P-Inhalte und
# heruntergeladene H5P-Bibliotheken sollen einen neuen Behälter überleben.
RUN apt-get update \
 && apt-get install -y --no-install-recommends mariadb-client \
 && rm -rf /var/lib/apt/lists/* \
 && chmod +x /usr/local/bin/eintritt.sh \
 && mkdir -p /daten/dateien /daten/privat \
 && ln -sfn /daten/dateien /opt/drupal/web/sites/default/files \
 && chown -R www-data:www-data /daten /opt/drupal/web/sites /opt/drupal/web/modules/custom /opt/drupal/web/themes/custom

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=180s --retries=6 \
  CMD php -r '$s=@file_get_contents("http://127.0.0.1/user/login"); exit($s===false?1:0);'

ENTRYPOINT ["/usr/local/bin/eintritt.sh"]
CMD ["apache2-foreground"]
