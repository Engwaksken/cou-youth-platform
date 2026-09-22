<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $gatewaySlug,
        PaymentWebhookService $service
    ): JsonResponse {
        $gateway = PaymentGateway::query()
            ->where('slug', $gatewaySlug)
            ->where('is_enabled', true)
            ->firstOrFail();

        $result = $service->process(
            $gateway,
            $request->all(),
            $request->header('X-COU-Signature'),
            $request->getContent()
        );

        return response()->json([
            'success' => $result['ok'],
        ], $result['status']);
    }
}
