<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthController extends Controller
{
    public function index()
    {
        $checks = [];

        try {
            DB::connection()->getPdo();
            $checks['database'] = ['ok' => true, 'message' => 'Database connection is healthy.'];
        } catch (Throwable $e) {
            $checks['database'] = ['ok' => false, 'message' => 'Database connection failed.'];
        }

        $checks['environment'] = [
            'ok' => app()->environment('production') && ! config('app.debug'),
            'message' => app()->environment().' / debug='.(config('app.debug') ? 'true' : 'false'),
        ];

        $checks['queue'] = [
            'ok' => config('queue.default') !== 'sync',
            'message' => 'Queue driver: '.config('queue.default'),
        ];

        $checks['storage'] = [
            'ok' => is_writable(storage_path()),
            'message' => is_writable(storage_path()) ? 'Storage is writable.' : 'Storage is not writable.',
        ];

        $requiredTables = [
            'users', 'organisation_units', 'contents', 'events', 'life_groups',
            'donation_campaigns', 'donations', 'platform_notifications', 'audit_logs',
        ];
        $missing = array_values(array_filter($requiredTables, fn ($table) => ! Schema::hasTable($table)));
        $checks['schema'] = [
            'ok' => $missing === [],
            'message' => $missing === [] ? 'Core tables are present.' : 'Missing: '.implode(', ', $missing),
        ];

        $healthy = collect($checks)->where('ok', true)->count();
        $unhealthy = count($checks) - $healthy;

        return view('admin.system.health', [
            'checks' => $checks,
            'release' => config('release'),
            'stats' => [
                'total' => count($checks),
                'healthy' => $healthy,
                'attention' => $unhealthy,
                'health_percent' => count($checks) > 0 ? (int) round(($healthy / count($checks)) * 100) : 0,
            ],
        ]);
    }
}
