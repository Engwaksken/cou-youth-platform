#!/usr/bin/env bash
set -euo pipefail

php artisan cou:readiness-check
php artisan migrate:status --no-interaction
php artisan route:list --path=api/v1 >/dev/null
php artisan test --testsuite=Feature

echo "Release verification passed."
