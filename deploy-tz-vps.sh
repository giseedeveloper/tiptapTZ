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

ssh -o StrictHostKeyChecking=no "${USER}@${HOST}" "
    set -e
    cd ${PROJECT_PATH}
    git pull origin ${BRANCH}
    test -f .env.docker || cp .env.docker.example .env.docker
    docker compose build --no-cache app queue
    # Always recreate PHP containers so the newly-built image is actually used.
    docker compose up -d --force-recreate app queue
    docker compose up -d
    # app_public volume keeps old Vite assets after rebuild — sync from host git checkout
    docker cp public/build/. tiptap_tz_app:/var/www/html/public/build/
    echo '--- Syncing app code into running container ---'
    docker cp resources/. tiptap_tz_app:/var/www/html/resources/
    docker cp app/. tiptap_tz_app:/var/www/html/app/
    docker cp routes/. tiptap_tz_app:/var/www/html/routes/
    docker cp config/. tiptap_tz_app:/var/www/html/config/
    docker exec tiptap_tz_app php artisan migrate --force --no-interaction
    docker exec tiptap_tz_app php artisan optimize:clear
    docker exec tiptap_tz_app php artisan config:cache
    docker exec tiptap_tz_app php artisan route:cache
    docker exec tiptap_tz_app php artisan view:cache

    echo '--- Restarting PHP-FPM to reset OPcache ---'
    # PHP runs with opcache.validate_timestamps=0, so files copied into a
    # running container are not picked up until the app process is restarted.
    docker compose restart app

    echo '--- Verifying the current portal theme ---'
    docker exec tiptap_tz_app sh -lc \
        \"grep -q 'portal-light' /var/www/html/resources/views/layouts/manager.blade.php \
        && test -f /var/www/html/resources/views/partials/portal-theme.blade.php\"

    docker ps --format '{{.Names}} {{.Status}}'
"

echo "=== TZ DEPLOY COMPLETE ==="
