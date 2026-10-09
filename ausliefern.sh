#!/bin/sh
# MekoTools — H5P-Studio ausliefern.
#
# Bringt den Stapel auf flip auf den neuesten Stand des festgenagelten Abbilds.
# Wird auf dem Arbeitsrechner ausgeführt; der Sprung geht über den VPS (n0ne).
#
#   ./ausliefern.sh                 # zieht das im Stapel festgenagelte Abbild
#   ./ausliefern.sh <verdauungswert># nagelt einen neuen Wert fest und liefert aus
#
set -eu

STAPEL=/poolio/docker/mekotools-studio
SPRUNG="-i /opt/data/.ssh/id_rsa -J n0ne"
WIRT=root@192.168.1.20

if [ $# -ge 1 ]; then
  WERT="$1"
  echo "[ausliefern] nagle Verdauungswert fest: $WERT"
  ssh $SPRUNG "$WIRT" "cd $STAPEL && cp -a docker-compose.yml docker-compose.yml.bak-\$(date +%Y%m%d-%H%M%S) && \
    sed -i -E 's#^    image: ghcr.io/mekotools/h5p-studio@sha256:.*#    image: ghcr.io/mekotools/h5p-studio@$WERT#' docker-compose.yml && \
    grep -n 'image: ghcr.io' docker-compose.yml"
fi

echo "[ausliefern] ziehen und neu erzeugen"
ssh $SPRUNG "$WIRT" "cd $STAPEL && docker compose pull web && docker compose up -d"

echo "[ausliefern] Zustand"
ssh $SPRUNG "$WIRT" "docker ps --filter name=mekotools-h5p-studio --format '{{.Names}}|{{.Status}}'"

echo "[ausliefern] Probe von aussen (erst nach einigen Sekunden aussagekraeftig)"
sleep 8
curl -sS -o /dev/null -w 'Startseite: HTTP %{http_code}\n' https://studio.mekotools.de/ || true
curl -sS -o /dev/null -w 'Bibliothek: HTTP %{http_code}\n' https://studio.mekotools.de/bibliothek || true
