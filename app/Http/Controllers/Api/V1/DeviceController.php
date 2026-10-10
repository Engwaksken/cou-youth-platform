<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyDeviceRequest;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Resources\DeviceRegistrationResource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function store(StoreDeviceRequest $request): DeviceRegistrationResource
    {
        $data = $request->validated();
        $tokenHash = hash('sha256', $data['push_token']);

        $now = now();

        DB::table('user_devices')->updateOrInsert(
            [
                'user_id' => $request->user()->id,
                'push_token_hash' => $tokenHash,
            ],
            [
                'push_token' => Crypt::encryptString($data['push_token']),
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'last_seen_at' => $now,
                'updated_at' => $now,
            ],
        );

        return new DeviceRegistrationResource('Device registered for notifications.');
    }

    public function destroy(DestroyDeviceRequest $request): DeviceRegistrationResource
    {
        $data = $request->validated();
        $tokenHash = hash('sha256', $data['push_token']);

        DB::table('user_devices')
            ->where('user_id', $request->user()->id)
            ->where('push_token_hash', $tokenHash)
            ->delete();

        return new DeviceRegistrationResource('Device notification registration removed.');
    }
}
