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
        $checks = [
            'database' => $this->databaseHealthy(),
            'storage' => $this->storageHealthy(),
            'app_key' => filled(config('app.key')),
            'debug_disabled' => ! (bool) config('app.debug'),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'service' => config('app.name', 'Church of Uganda Youth Platform'),
            'environment' => app()->environment(),
            'time' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
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
