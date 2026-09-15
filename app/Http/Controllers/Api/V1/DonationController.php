<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class DonationController extends Controller
{
    public function campaigns(): JsonResponse
    {
        $campaigns = DonationCampaign::query()
            ->where('status', 'published')
            ->withSum(['donations as amount_raised' => fn ($q) => $q->where('status', 'successful')], 'amount')
            ->latest()
            ->paginate(12);

        return response()->json(['success' => true, 'data' => $campaigns]);
    }

    public function gateways(): JsonResponse
    {
        $gateways = PaymentGateway::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get(['id','name','slug','provider','currency','is_test_mode']);

        return response()->json(['success' => true, 'data' => $gateways]);
    }

    public function store(Request $request, PaymentGatewayManager $payments): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => ['nullable','exists:donation_campaigns,id'],
            'payment_gateway_id' => ['required','exists:payment_gateways,id'],
            'amount' => ['required','numeric','min:100'],
            'currency' => ['required','string','size:3'],
            'is_anonymous' => ['sometimes','boolean'],
            'donor_name' => ['nullable','string','max:150'],
            'donor_email' => ['nullable','email','max:190'],
            'donor_phone' => ['nullable','string','max:40'],
        ]);

        $gateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);

        $donation = Donation::create([
            ...$validated,
            'reference' => (string) Str::uuid(),
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        try {
            $payment = $payments->initializeDonation($donation, $gateway);
            return response()->json(['success' => true, 'data' => ['donation' => $donation, 'payment' => $payment]], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'We could not start your payment. Please try again or choose another payment method.',
            ], 422);
        }
    }
}
