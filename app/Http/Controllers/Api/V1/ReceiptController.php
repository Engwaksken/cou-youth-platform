<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReceiptController extends Controller
{
    public function show(Request $request, Donation $donation): JsonResponse
    {
        abort_unless(
            (int) $donation->user_id === (int) $request->user()->id,
            404
        );

        abort_unless(
            in_array($donation->status, ['successful', 'paid', 'completed'], true),
            404
        );

        return response()->json([
            'receipt_number' => $donation->receipt_number,
            'reference' => $donation->reference,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'paid_at' => $donation->paid_at,
            'donor_name' => $donation->is_anonymous
                ? 'Anonymous'
                : ($donation->donor_name ?: $request->user()->name),
        ]);
    }
}
