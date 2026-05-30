#!/usr/bin/env bash
set -euo pipefail

# Run on VPS inside /root/TIPTAP after DNS points to this server.
# Usage: CERTBOT_EMAIL=you@example.com bash scripts/enable-ssl-tz.sh

cd "$(dirname "$0")/.."

EMAIL="${CERTBOT_EMAIL:-admin@tiptapafrica.co.tz}"

docker compose run --rm certbot certonly --webroot \
  -w /var/www/certbot \
  -d tiptapafrica.co.tz \
  -d www.tiptapafrica.co.tz \
  --email "$EMAIL" \
  --agree-tos \
  --no-eff-email \
  --force-renewal

cp docker/nginx/default-ssl.conf docker/nginx/default.conf
docker compose restart nginx

echo "SSL enabled. Test: https://tiptapafrica.co.tz"
