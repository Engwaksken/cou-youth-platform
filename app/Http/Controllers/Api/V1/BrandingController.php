<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Branding\BrandingService;
use Illuminate\Http\JsonResponse;

final class BrandingController extends Controller
{
    public function __invoke(BrandingService $branding): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $branding->data(),
        ]);
    }
}
