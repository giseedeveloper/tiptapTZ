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
    docker compose up -d
    # app_public volume keeps old Vite assets after rebuild — sync from fresh image
    CID=\$(docker create tiptap-app)
    docker cp \"\$CID:/var/www/html/public/build\" /tmp/tiptap_build_sync
    docker rm \"\$CID\"
    docker cp /tmp/tiptap_build_sync/. tiptap_tz_app:/var/www/html/public/build/
    rm -rf /tmp/tiptap_build_sync
    docker exec tiptap_tz_app php artisan migrate --force --no-interaction
    docker exec tiptap_tz_app php artisan config:cache
    docker exec tiptap_tz_app php artisan route:cache
    docker exec tiptap_tz_app php artisan view:cache
    docker ps --format '{{.Names}} {{.Status}}'
"

echo "=== TZ DEPLOY COMPLETE ==="
