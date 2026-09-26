<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

final class YouthDonationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $campaigns = Schema::hasTable('donation_campaigns')
            ? DonationCampaign::query()
                ->whereIn('status', ['published', 'active', 'open'])
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('title')
                ->get()
            : collect();

        $gateways = Schema::hasTable('payment_gateways')
            ? PaymentGateway::query()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'provider', 'currency', 'is_test_mode'])
            : collect();

        $donations = Schema::hasTable('donations')
            ? Donation::query()
                ->with(['campaign:id,title', 'gateway:id,name,provider,currency'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10, ['*'], 'donations_page')
                ->withQueryString()
            : collect();

        $membershipPayments = Schema::hasTable('membership_fee_payments')
            ? DB::table('membership_fee_payments as p')
                ->leftJoin('membership_fees as f', 'f.id', '=', 'p.membership_fee_id')
                ->where('p.user_id', $user->id)
                ->select('p.*', 'f.name as fee_name', 'f.year as fee_year')
                ->orderByDesc('p.paid_at')
                ->paginate(10, ['*'], 'contributions_page')
                ->withQueryString()
            : null;

        $successful = Schema::hasTable('donations')
            ? Donation::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['successful', 'paid', 'completed'])
                ->get(['amount', 'currency'])
            : collect();

        $pendingCount = Schema::hasTable('donations')
            ? Donation::query()->where('user_id', $user->id)->where('status', 'pending')->count()
            : 0;

        return view('youth.donations', [
            'campaigns' => $campaigns,
            'gateways' => $gateways,
            'donations' => $donations,
            'membershipPayments' => $membershipPayments,
            'selectedCampaign' => $request->integer('campaign') ?: null,
            'stats' => [
                'donation_count' => Schema::hasTable('donations') ? Donation::where('user_id', $user->id)->count() : 0,
                'successful_ugx' => (float) $successful->where('currency', 'UGX')->sum(fn ($item) => (float) $item->amount),
                'pending' => $pendingCount,
                'other_contributions' => $membershipPayments?->total() ?? 0,
            ],
        ]);
    }

    public function store(Request $request, PaymentGatewayManager $payments): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => ['nullable', 'integer', 'exists:donation_campaigns,id'],
            'payment_gateway_id' => ['required', 'integer', 'exists:payment_gateways,id'],
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'size:3'],
            'donor_phone' => ['nullable', 'string', 'max:40'],
            'is_anonymous' => ['sometimes', 'boolean'],
        ]);

        $validated['currency'] = strtoupper($validated['currency']);
        $validated['is_anonymous'] = $request->boolean('is_anonymous');

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
                'currency' => sprintf('The selected payment method accepts %s payments.', strtoupper((string) $gateway->currency)),
            ]);
        }

        if (! empty($validated['campaign_id'])) {
            $campaign = DonationCampaign::query()
                ->whereKey($validated['campaign_id'])
                ->whereIn('status', ['published', 'active', 'open'])
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->first();

            if (! $campaign) {
                throw ValidationException::withMessages([
                    'campaign_id' => 'This donation campaign is not currently accepting donations.',
                ]);
            }

            if (strtoupper((string) $campaign->currency) !== $validated['currency']) {
                throw ValidationException::withMessages([
                    'currency' => sprintf('This campaign accepts donations in %s.', strtoupper((string) $campaign->currency)),
                ]);
            }

            if ($validated['is_anonymous'] && ! $campaign->allow_anonymous) {
                throw ValidationException::withMessages([
                    'is_anonymous' => 'Anonymous donations are not enabled for this campaign.',
                ]);
            }
        }

        $user = $request->user();
        $provider = strtolower((string) $gateway->provider);
        if (in_array($provider, ['mtn_momo', 'mtn', 'mtn_mobile_money', 'airtel_money', 'airtel', 'airtel_momo', 'mobile_money'], true)
            && blank($validated['donor_phone'])) {
            throw ValidationException::withMessages([
                'donor_phone' => 'A mobile money phone number is required for this payment method.',
            ]);
        }

        $donation = Donation::create([
            'reference' => (string) Str::uuid(),
            'campaign_id' => $validated['campaign_id'] ?? null,
            'user_id' => $user->id,
            'payment_gateway_id' => $gateway->id,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'pending',
            'is_anonymous' => $validated['is_anonymous'],
            'donor_name' => $validated['is_anonymous'] ? null : $user->name,
            'donor_email' => $user->email,
            'donor_phone' => $validated['donor_phone'] ?? null,
        ]);

        try {
            $payment = $payments->initializeDonation($donation, $gateway);

            $donation->forceFill([
                'gateway_response' => is_array($payment['provider_response'] ?? null)
                    ? $payment['provider_response']
                    : $donation->gateway_response,
                'external_transaction_id' => $payment['provider_reference'] ?? $donation->external_transaction_id,
            ])->save();

            $checkoutUrl = trim((string) ($payment['checkout_url'] ?? ''));
            if ($checkoutUrl !== '') {
                return redirect()->away($this->safeCheckoutUrl($checkoutUrl));
            }

            return redirect()
                ->route('youth.donations')
                ->with('success', (string) ($payment['message'] ?? 'Donation payment started successfully.'));
        } catch (Throwable $exception) {
            report($exception);
            $donation->forceFill(['status' => 'failed'])->save();

            return back()
                ->withInput()
                ->withErrors(['payment' => 'We could not start your payment. Please try again or choose another payment method.']);
        }
    }

    private function safeCheckoutUrl(string $url): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Payment provider returned an invalid checkout URL.');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $allowedSchemes = app()->environment('production') ? ['https'] : ['http', 'https'];

        if (! in_array($scheme, $allowedSchemes, true)) {
            throw new RuntimeException('Payment provider returned an insecure checkout URL.');
        }

        return $url;
    }
}
