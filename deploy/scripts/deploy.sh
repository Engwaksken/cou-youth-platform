#!/usr/bin/env bash
set -Eeuo pipefail

maintenance=0

restore_application() {
    exit_code=$?

    if [[ "$maintenance" -eq 1 ]]; then
        php artisan up >/dev/null 2>&1 || true
    fi

    if [[ "$exit_code" -ne 0 ]]; then
        echo "Deployment failed with exit code ${exit_code}. The application was returned online where possible." >&2
    fi
}

trap restore_application EXIT

for command in php composer npm mysqldump; do
    if ! command -v "$command" >/dev/null 2>&1; then
        echo "$command is required for production deployment." >&2
        exit 1
    fi
done

# Build frontend assets before downtime. Prefer npm ci once a lock file is committed.
if [[ -f package-lock.json ]]; then
    npm ci --no-audit --no-fund
else
    echo "WARNING: package-lock.json is missing; using npm install. Commit a generated lock file for reproducible releases." >&2
    npm install --no-audit --no-fund
fi
npm run build

# Never run production schema migrations without a fresh database snapshot.
php artisan cou:backup --database-only

php artisan down --render="errors::503" || php artisan down
maintenance=1

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
maintenance=0
trap - EXIT

echo "Church of Uganda Youth Platform deployment completed successfully."
