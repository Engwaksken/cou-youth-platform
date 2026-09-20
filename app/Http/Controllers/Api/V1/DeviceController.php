<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform' => ['required', 'in:android,ios,web'],
            'push_token' => ['required', 'string', 'max:4096'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $now = now();

        DB::table('user_devices')->updateOrInsert(
            [
                'user_id' => $request->user()->id,
                'push_token' => $data['push_token'],
            ],
            [
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'last_seen_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        return response()->json([
            'message' => 'Device registered for notifications.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'push_token' => ['required', 'string', 'max:4096'],
        ]);

        DB::table('user_devices')
            ->where('user_id', $request->user()->id)
            ->where('push_token', $data['push_token'])
            ->delete();

        return response()->json([
            'message' => 'Device notification registration removed.',
        ]);
    }
}
