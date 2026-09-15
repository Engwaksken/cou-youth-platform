# Rollback procedure

1. Put the application in maintenance mode.
2. Restore the most recent verified database backup if the release changed data incompatibly.
3. Restore the previous application release/symlink.
4. Run `php artisan optimize:clear` and then cache config/routes/views again.
5. Restart queue workers.
6. Run `php artisan cou:readiness-check`.
7. Bring the application online and verify `/api/v1/health`.

Do not roll back a financial transaction by deleting payment rows. Use an auditable refund/reversal workflow with the configured gateway.
