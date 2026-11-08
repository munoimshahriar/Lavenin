#!/bin/bash
# Push the current commit to lavenin.com (Hostinger). Run from the project root in Git Bash:
#   bash scripts/deploy-hostinger.sh
# Needs the "lavenin" host in ~/.ssh/config (key: ~/.ssh/lavenin_hostinger).
set -euo pipefail
cd "$(dirname "$0")/.."

if [ -n "$(git status --porcelain)" ]; then
  echo "Commit your changes first (only committed code is deployed)."; exit 1
fi

echo "== Build assets"
npm run build >/dev/null

echo "== Package $(git rev-parse --short HEAD)"
TMP=$(mktemp -d)
git archive --format=tar HEAD -o "$TMP/lavenin.tar"
tar --force-local -rf "$TMP/lavenin.tar" public/build
gzip -f "$TMP/lavenin.tar"

echo "== Upload"
scp -q "$TMP/lavenin.tar.gz" lavenin:~/lavenin.tar.gz
rm -rf "$TMP"

echo "== Install on server"
ssh lavenin 'set -e
cd ~/lavenin
php artisan down --retry=30 || true
rm -rf public/build
tar -xzf ~/lavenin.tar.gz -C ~/lavenin && rm -f ~/lavenin.tar.gz
# Hostinger disables proc_open/exec for Composer scripts, so run the hooks ourselves.
composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts 2>&1 | tail -1
php artisan package:discover --no-interaction >/dev/null
ln -sfn ~/lavenin/storage/app/public public/storage
php artisan migrate --force --no-interaction
php artisan optimize:clear >/dev/null
php artisan optimize --no-interaction >/dev/null
php artisan filament:optimize --no-interaction >/dev/null || true
php artisan up'

echo "== Done: https://lavenin.com"
