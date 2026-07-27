#!/bin/bash
set -e

# TIPTAP Tanzania – VPS deploy (Docker)
# Host: 164.92.242.245 | Domain: tiptapafrica.co.tz
# Requires SSH key access to root@164.92.242.245

HOST="${TIPTAP_TZ_HOST:-164.92.242.245}"
USER="${TIPTAP_TZ_USER:-root}"
PROJECT_PATH="${TIPTAP_TZ_PATH:-/root/TIPTAP}"
BRANCH="${TIPTAP_TZ_BRANCH:-main}"

echo "=== TIPTAP TZ DEPLOY ==="

ssh -o StrictHostKeyChecking=accept-new "${USER}@${HOST}" "
    set -e
    cd ${PROJECT_PATH}
    git pull origin ${BRANCH}
    test -f .env.docker || {
        echo 'ERROR: .env.docker is missing. Create it from the example and set production secrets.'
        exit 1
    }
    grep -Eq '^MYSQL_ROOT_PASSWORD=.{24,}$' .env.docker \
        && ! grep -Eqi '^MYSQL_ROOT_PASSWORD=.*(change_me|password)' .env.docker || {
        echo 'ERROR: Set a unique MYSQL_ROOT_PASSWORD of at least 24 characters in .env.docker.'
        exit 1
    }
    grep -Eq '^WHATSAPP_APP_SECRET=.+$' .env.docker || {
        echo 'ERROR: WHATSAPP_APP_SECRET is required for production webhook verification.'
        exit 1
    }
    docker compose build --no-cache app queue
    docker compose up -d --force-recreate --remove-orphans

    echo '--- Verifying the current portal theme ---'
    docker exec tiptap_tz_app sh -lc \
        \"grep -q 'portal-light' /var/www/html/resources/views/layouts/manager.blade.php \
        && test -f /var/www/html/resources/views/partials/portal-theme.blade.php\"

    curl --fail --silent --show-error --retry 12 --retry-delay 5 --retry-all-errors \
        https://tiptapafrica.co.tz/up >/dev/null
    test \"\$(curl --silent --output /dev/null --write-out '%{http_code}' https://tiptapafrica.co.tz/run_migrations.php)\" = '404'
    test \"\$(curl --silent --output /dev/null --write-out '%{http_code}' https://tiptapafrica.co.tz/database/)\" = '404'
    docker ps --format '{{.Names}} {{.Status}}'
"

echo "=== TZ DEPLOY COMPLETE ==="
