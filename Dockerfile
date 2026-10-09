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
RUN composer require --no-interaction --no-progress --with-all-dependencies \
      "drupal/h5p:2.0.0-beta1" \
      "drupal/openid_connect:3.0.0-alpha9" \
      "drush/drush:^13.6" \
 && composer check-platform-reqs \
 && ls -d web/modules/contrib/h5p web/modules/contrib/h5peditor web/modules/contrib/openid_connect

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
COPY studio/ /opt/drupal/web/modules/custom/mekotools_studio/
COPY docker/eintritt.sh /usr/local/bin/eintritt.sh

# Der Dateibereich liegt in einem Datenträger, nicht im Abbild: H5P-Inhalte und
# heruntergeladene H5P-Bibliotheken sollen einen neuen Behälter überleben.
RUN chmod +x /usr/local/bin/eintritt.sh \
 && mkdir -p /daten/dateien /daten/privat \
 && ln -sfn /daten/dateien /opt/drupal/web/sites/default/files \
 && chown -R www-data:www-data /daten /opt/drupal/web/sites /opt/drupal/web/modules/custom

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=180s --retries=6 \
  CMD php -r '$s=@file_get_contents("http://127.0.0.1/user/login"); exit($s===false?1:0);'

ENTRYPOINT ["/usr/local/bin/eintritt.sh"]
CMD ["apache2-foreground"]
