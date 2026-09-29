<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class CouReadinessCheck extends Command
{
    protected $signature = 'cou:readiness-check';
    protected $description = 'Validate critical Church of Uganda Youth Platform production requirements';

    public function handle(): int
    {
        $appUrl = (string) config('app.url');
        $queue = (string) config('queue.default');
        $cache = (string) config('cache.default');
        $session = (string) config('session.driver');

        $checks = [
            ['APP_ENV is production', app()->environment('production')],
            ['APP_DEBUG is disabled', ! (bool) config('app.debug')],
            ['APP_KEY is configured', filled(config('app.key'))],
            ['APP_URL uses HTTPS', str_starts_with($appUrl, 'https://')],
            ['APP_URL is not an example host', ! str_contains($appUrl, 'example.')],
            ['Secure session cookies are enabled', (bool) config('session.secure')],
            ['HTTP-only session cookies are enabled', (bool) config('session.http_only')],
            ['Session encryption is enabled', (bool) config('session.encrypt')],
            ['Database is reachable', $this->databaseReachable()],
            ['users table exists', Schema::hasTable('users')],
            ['organisation_units table exists', Schema::hasTable('organisation_units')],
            ['guardian_consents table exists', Schema::hasTable('guardian_consents')],
            ['payment_gateways table exists', Schema::hasTable('payment_gateways')],
            ['donations table exists', Schema::hasTable('donations')],
            ['payment_webhook_logs table exists', Schema::hasTable('payment_webhook_logs')],
            ['audit_logs table exists', Schema::hasTable('audit_logs')],
            ['Queue is not sync', $queue !== 'sync'],
            ['Database queue table exists when required', $queue !== 'database' || Schema::hasTable('jobs')],
            ['Cache is persistent', ! in_array($cache, ['array', 'null'], true)],
            ['Database cache table exists when required', $cache !== 'database' || Schema::hasTable('cache')],
            ['Session storage is persistent', ! in_array($session, ['array', 'file'], true)],
            ['Database session table exists when required', $session !== 'database' || Schema::hasTable('sessions')],
            ['Mail transport configured', ! in_array(config('mail.default'), ['log', 'array'], true)],
            ['Storage directory is writable', is_writable(storage_path())],
            ['Log directory is writable', is_dir(storage_path('logs')) && is_writable(storage_path('logs'))],
            ['Public storage link/directory exists', is_link(public_path('storage')) || is_dir(public_path('storage'))],
            ['Compiled frontend assets exist', is_file(public_path('build/manifest.json'))],
        ];

        $failed = 0;
        foreach ($checks as [$label, $passed]) {
            $passed ? $this->components->info($label) : $this->components->error($label);
            $failed += $passed ? 0 : 1;
        }

        if ($failed > 0) {
            $this->error("{$failed} production readiness check(s) failed.");
            return self::FAILURE;
        }

        $this->info('All critical production readiness checks passed.');
        return self::SUCCESS;
    }

    private function databaseReachable(): bool
    {
        try {
            DB::select('select 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
