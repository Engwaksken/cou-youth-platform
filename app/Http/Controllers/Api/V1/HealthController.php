<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $environment = app()->environment();

        $checks = [
            'database' => $this->databaseHealthy(),
            'storage' => $this->storageHealthy(),
            'app_key' => filled(config('app.key')),
            'debug_disabled' => ! (bool) config('app.debug'),
        ];

        // Database and storage are runtime-readiness checks everywhere.
        // APP_KEY and debug mode are production-hardening checks: they should
        // be visible in local/testing health output without making the health
        // endpoint itself unavailable during development or automated tests.
        $criticalChecks = [
            $checks['database'],
            $checks['storage'],
        ];

        if ($environment === 'production') {
            $criticalChecks[] = $checks['app_key'];
            $criticalChecks[] = $checks['debug_disabled'];
        }

        $ready = ! in_array(false, $criticalChecks, true);
        $allChecksPass = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $allChecksPass ? 'healthy' : 'degraded',
            'service' => config('app.name', 'Church of Uganda Youth Platform'),
            'environment' => $environment,
            'time' => now()->toIso8601String(),
            'checks' => $checks,
        ], $ready ? 200 : 503);
    }

    private function databaseHealthy(): bool
    {
        try {
            DB::select('select 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function storageHealthy(): bool
    {
        try {
            Storage::disk(config('filesystems.default'))->exists('.');
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
