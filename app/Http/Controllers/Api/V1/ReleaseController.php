<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ReleaseController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'version' => config('release.version'),
                'build' => config('release.build'),
                'channel' => config('release.channel'),
                'released_at' => config('release.released_at'),
                'minimum_mobile_version' => config('release.minimum_mobile_version'),
                'maintenance_message' => config('release.maintenance_message'),
            ],
        ]);
    }
}
