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
        $checks = [
            ['APP_ENV is production', app()->environment('production')],
            ['APP_DEBUG is disabled', ! (bool) config('app.debug')],
            ['APP_KEY is configured', filled(config('app.key'))],
            ['APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://')],
            ['Database is reachable', $this->databaseReachable()],
            ['users table exists', Schema::hasTable('users')],
            ['organisation_units table exists', Schema::hasTable('organisation_units')],
            ['guardian_consents table exists', Schema::hasTable('guardian_consents')],
            ['payment_gateways table exists', Schema::hasTable('payment_gateways')],
            ['audit_logs table exists', Schema::hasTable('audit_logs')],
            ['Queue is not sync', config('queue.default') !== 'sync'],
            ['Mail transport configured', ! in_array(config('mail.default'), ['log', 'array'], true)],
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
