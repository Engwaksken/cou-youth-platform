#!/usr/bin/env bash
set -euo pipefail

php artisan down --render="errors::503" || true
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cou:readiness-check
php artisan queue:restart || true
php artisan up

echo "Church of Uganda Youth Platform deployment completed."
