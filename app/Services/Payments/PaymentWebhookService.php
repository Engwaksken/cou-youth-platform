<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentWebhookLog;
use Illuminate\Support\Arr;
use Throwable;

final class PaymentWebhookService
{
    public function __construct(
        private readonly PaymentGatewayManager $payments
    ) {
    }

    public function process(
        PaymentGateway $gateway,
        array $payload,
        ?string $signature,
        string $rawBody = ''
    ): array {
        $provider = strtolower((string) $gateway->provider);
        $mobileProvider = in_array($provider, [
            'mtn_momo', 'mtn', 'mtn_mobile_money',
            'airtel_money', 'airtel', 'airtel_momo',
        ], true);

        $reference = $this->extractReference($payload);
        $signatureValid = $mobileProvider
            ? false
            : $this->validSignature($gateway, $signature, $rawBody, $payload);

        $log = PaymentWebhookLog::create([
            'payment_gateway_id' => $gateway->id,
            'provider' => $gateway->provider,
            'event_type' => Arr::get($payload, 'event') ?? Arr::get($payload, 'type'),
            'external_reference' => $reference ?: null,
            'signature' => $signature,
            'signature_valid' => $signatureValid,
            'payload' => $payload,
            'processing_status' => $mobileProvider || $signatureValid ? 'processing' : 'rejected',
        ]);

        if (! $mobileProvider && ! $signatureValid) {
            $log->update(['error_message' => 'Invalid webhook signature']);

            return ['ok' => false, 'status' => 401];
        }

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

        if ($mobileProvider) {
            return $this->processVerifiedMobileCallback($gateway, $donation, $payload, $log);
        }

        return $this->applyStatus(
            $donation,
            $this->mapStatus((string) Arr::get($payload, 'status', 'pending')),
            Arr::get($payload, 'transaction_id') ?? Arr::get($payload, 'transactionId'),
            $payload,
            $log
        );
    }

    private function processVerifiedMobileCallback(
        PaymentGateway $gateway,
        Donation $donation,
        array $callbackPayload,
        PaymentWebhookLog $log
    ): array {
        try {
            // MTN and Airtel callbacks are treated as a signal to verify the transaction
            // against the authenticated provider status API before changing local payment state.
            $providerStatus = $this->payments->queryDonationStatus($donation, $gateway);

            $log->update(['signature_valid' => true]);

            return $this->applyStatus(
                $donation,
                (string) ($providerStatus['status'] ?? 'pending'),
                $providerStatus['transaction_id'] ?? null,
                [
                    'callback' => $callbackPayload,
                    'verified_status' => $providerStatus['provider_response'] ?? [],
                ],
                $log
            );
        } catch (Throwable $exception) {
            report($exception);

            $log->update([
                'processing_status' => 'failed',
                'error_message' => 'Provider status verification failed',
            ]);

            // Acknowledge the callback so the provider does not repeatedly retry while the
            // platform can still recover payment state through the explicit verify endpoint.
            return ['ok' => true, 'status' => 202];
        }
    }

    private function applyStatus(
        Donation $donation,
        string $newStatus,
        mixed $transactionId,
        array $providerResponse,
        PaymentWebhookLog $log
    ): array {
        $newStatus = $this->mapStatus($newStatus);

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
            'external_transaction_id' => filled($transactionId)
                ? (string) $transactionId
                : $donation->external_transaction_id,
            'gateway_response' => $providerResponse,
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

    private function validSignature(
        PaymentGateway $gateway,
        ?string $signature,
        string $rawBody,
        array $payload
    ): bool {
        $secret = (string) Arr::get($gateway->settings, 'webhook_secret', '');
        $signedBody = $rawBody !== ''
            ? $rawBody
            : (string) json_encode($payload, JSON_UNESCAPED_SLASHES);

        return $secret !== ''
            && is_string($signature)
            && $signature !== ''
            && hash_equals(hash_hmac('sha256', $signedBody, $secret), $signature);
    }

    private function extractReference(array $payload): string
    {
        return trim((string) (
            Arr::get($payload, 'reference')
            ?? Arr::get($payload, 'externalId')
            ?? Arr::get($payload, 'external_id')
            ?? Arr::get($payload, 'transaction.reference')
            ?? Arr::get($payload, 'transaction.id')
            ?? Arr::get($payload, 'data.transaction.reference')
            ?? Arr::get($payload, 'data.transaction.id')
            ?? ''
        ));
    }

    private function mapStatus(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'PAID', 'SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'TS' => 'successful',
            'FAILED', 'REJECTED', 'TF' => 'failed',
            'CANCELLED', 'CANCELED' => 'cancelled',
            'REFUNDED' => 'refunded',
            default => 'pending',
        };
    }
}
