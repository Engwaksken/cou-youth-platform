<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class DonationController extends Controller
{
    public function campaigns(): JsonResponse
    {
        $campaigns = DonationCampaign::query()
            ->whereIn('status', ['published', 'active', 'open'])
            ->where(function ($query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->withSum([
                'donations as amount_raised' => fn ($query) => $query->whereIn('status', [
                    'successful',
                    'paid',
                    'completed',
                ]),
            ], 'amount')
            ->latest()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function gateways(): JsonResponse
    {
        $gateways = PaymentGateway::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'slug',
                'provider',
                'currency',
                'is_test_mode',
            ]);

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    public function store(Request $request, PaymentGatewayManager $payments): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => ['nullable', 'integer', 'exists:donation_campaigns,id'],
            'payment_gateway_id' => ['required', 'integer', 'exists:payment_gateways,id'],
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'size:3'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'donor_name' => ['nullable', 'string', 'max:150'],
            'donor_email' => ['nullable', 'email', 'max:190'],
            'donor_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $validated['currency'] = strtoupper($validated['currency']);
        $validated['is_anonymous'] = (bool) ($validated['is_anonymous'] ?? false);

        $gateway = PaymentGateway::query()
            ->whereKey($validated['payment_gateway_id'])
            ->where('is_enabled', true)
            ->first();

        if (! $gateway) {
            throw ValidationException::withMessages([
                'payment_gateway_id' => 'The selected payment method is currently unavailable.',
            ]);
        }

        if (strtoupper((string) $gateway->currency) !== $validated['currency']) {
            throw ValidationException::withMessages([
                'currency' => sprintf(
                    'The selected payment method accepts %s payments.',
                    strtoupper((string) $gateway->currency)
                ),
            ]);
        }

        $campaign = null;

        if (! empty($validated['campaign_id'])) {
            $campaign = DonationCampaign::query()
                ->whereKey($validated['campaign_id'])
                ->whereIn('status', ['published', 'active', 'open'])
                ->where(function ($query): void {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->first();

            if (! $campaign) {
                throw ValidationException::withMessages([
                    'campaign_id' => 'This donation campaign is not currently accepting donations.',
                ]);
            }

            if (strtoupper((string) $campaign->currency) !== $validated['currency']) {
                throw ValidationException::withMessages([
                    'currency' => sprintf(
                        'This campaign accepts donations in %s.',
                        strtoupper((string) $campaign->currency)
                    ),
                ]);
            }

            if ($validated['is_anonymous'] && ! $campaign->allow_anonymous) {
                throw ValidationException::withMessages([
                    'is_anonymous' => 'Anonymous donations are not enabled for this campaign.',
                ]);
            }
        }

        if (! $validated['is_anonymous'] && ! $request->user() && empty($validated['donor_name'])) {
            throw ValidationException::withMessages([
                'donor_name' => 'Please provide your name or choose anonymous donation where available.',
            ]);
        }

        if ($validated['is_anonymous']) {
            $validated['donor_name'] = null;
        }

        $donation = Donation::create([
            ...$validated,
            'reference' => (string) Str::uuid(),
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        try {
            $payment = $payments->initializeDonation($donation, $gateway);

            return response()->json([
                'success' => true,
                'message' => 'Donation payment started successfully.',
                'data' => [
                    'donation' => $donation->fresh(),
                    'payment' => $payment,
                ],
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            $donation->forceFill([
                'status' => 'failed',
            ])->save();

            return response()->json([
                'success' => false,
                'message' => 'We could not start your payment. Please try again or choose another payment method.',
            ], 422);
        }
    }
}
