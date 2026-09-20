<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentWebhookLog;
use Illuminate\Support\Arr;

final class PaymentWebhookService
{
    public function process(
        PaymentGateway $gateway,
        array $payload,
        ?string $signature,
        string $rawBody = ''
    ): array {
        $secret = (string) Arr::get($gateway->settings, 'webhook_secret', '');
        $signedBody = $rawBody !== ''
            ? $rawBody
            : (string) json_encode($payload, JSON_UNESCAPED_SLASHES);

        $valid = $secret !== ''
            && is_string($signature)
            && $signature !== ''
            && hash_equals(hash_hmac('sha256', $signedBody, $secret), $signature);

        $log = PaymentWebhookLog::create([
            'payment_gateway_id' => $gateway->id,
            'provider' => $gateway->provider,
            'event_type' => Arr::get($payload, 'event'),
            'external_reference' => Arr::get($payload, 'reference'),
            'signature' => $signature,
            'signature_valid' => $valid,
            'payload' => $payload,
            'processing_status' => $valid ? 'processing' : 'rejected',
        ]);

        if (! $valid) {
            $log->update(['error_message' => 'Invalid webhook signature']);

            return ['ok' => false, 'status' => 401];
        }

        $reference = trim((string) (Arr::get($payload, 'reference') ?? ''));

        if ($reference === '') {
            $log->update([
                'processing_status' => 'ignored',
                'error_message' => 'Donation reference missing',
            ]);

            return ['ok' => true, 'status' => 202];
        }

        $donation = Donation::query()
            ->where('payment_gateway_id', $gateway->id)
            ->where('reference', $reference)
            ->first();

        if (! $donation) {
            $log->update([
                'processing_status' => 'ignored',
                'error_message' => 'Donation reference not found',
            ]);

            return ['ok' => true, 'status' => 202];
        }

        $providerStatus = strtolower(trim((string) Arr::get($payload, 'status', 'pending')));
        $statusMap = [
            'paid' => 'successful',
            'success' => 'successful',
            'successful' => 'successful',
            'completed' => 'successful',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'refunded',
            'pending' => 'pending',
            'processing' => 'pending',
        ];

        $newStatus = $statusMap[$providerStatus] ?? 'pending';

        // Never allow a duplicate or delayed callback to downgrade a completed payment.
        if (in_array($donation->status, ['successful', 'paid', 'completed'], true)
            && $newStatus !== 'refunded') {
            $log->update(['processing_status' => 'processed']);

            return [
                'ok' => true,
                'status' => 200,
                'donation' => $donation->fresh(),
                'idempotent' => true,
            ];
        }

        $attributes = [
            'status' => $newStatus,
            'external_transaction_id' => Arr::get($payload, 'transaction_id')
                ?? Arr::get($payload, 'transactionId')
                ?? $donation->external_transaction_id,
            'gateway_response' => $payload,
        ];

        if ($newStatus === 'successful') {
            $attributes['paid_at'] = $donation->paid_at ?: now();
            $attributes['receipt_number'] = $donation->receipt_number
                ?: 'COU-RCP-'.now()->format('Ymd').'-'.str_pad((string) $donation->id, 7, '0', STR_PAD_LEFT);
        }

        $donation->update($attributes);
        $log->update(['processing_status' => 'processed']);

        return [
            'ok' => true,
            'status' => 200,
            'donation' => $donation->fresh(),
        ];
    }
}
