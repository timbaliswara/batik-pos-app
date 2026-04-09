#!/usr/bin/env bash

set -euo pipefail

export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

APP_DIR="/var/www/batik-pos-app"
BRANCH="main"
APP_USER="rootilh"

exec 9>/tmp/batik-pos-deploy.lock
flock -n 9 || exit 0

cd "$APP_DIR"

sudo -u "$APP_USER" git fetch origin "$BRANCH"

LOCAL_COMMIT="$(sudo -u "$APP_USER" git rev-parse HEAD)"
REMOTE_COMMIT="$(sudo -u "$APP_USER" git rev-parse "origin/$BRANCH")"

if [[ "$LOCAL_COMMIT" == "$REMOTE_COMMIT" ]]; then
    exit 0
fi

sudo -u "$APP_USER" git reset --hard "$REMOTE_COMMIT"

sudo -u "$APP_USER" composer install --no-dev --optimize-autoloader
sudo -u "$APP_USER" npm ci
sudo -u "$APP_USER" npm run build
sudo -u "$APP_USER" php artisan migrate --force
sudo -u "$APP_USER" php artisan storage:link || true
sudo -u "$APP_USER" php artisan optimize:clear
sudo -u "$APP_USER" php artisan config:cache
sudo -u "$APP_USER" php artisan route:cache
sudo -u "$APP_USER" php artisan view:cache
sudo -u "$APP_USER" php artisan queue:restart || true

sudo chown -R "$APP_USER":www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
sudo chmod -R ug+rwx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
