<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Donation;

final class DonationStatusUpdater
{
    /**
     * Apply a provider-confirmed payment status while protecting completed payments
     * from accidental downgrades caused by retries, delayed callbacks or polling.
     */
    public function apply(
        Donation $donation,
        string $status,
        mixed $transactionId = null,
        ?array $providerResponse = null
    ): Donation {
        $newStatus = $this->normaliseStatus($status);
        $currentStatus = $this->normaliseStatus((string) $donation->status);

        if ($currentStatus === 'refunded' && $newStatus !== 'refunded') {
            return $donation->fresh() ?? $donation;
        }

        if ($this->isSuccessful($currentStatus) && $newStatus !== 'refunded') {
            return $donation->fresh() ?? $donation;
        }

        if (in_array($currentStatus, ['failed', 'cancelled'], true) && $newStatus === 'pending') {
            return $donation->fresh() ?? $donation;
        }

        $attributes = [
            'status' => $newStatus,
        ];

        if (filled($transactionId)) {
            $attributes['external_transaction_id'] = (string) $transactionId;
        }

        if ($providerResponse !== null) {
            $attributes['gateway_response'] = $providerResponse;
        }

        if ($newStatus === 'successful') {
            $attributes['paid_at'] = $donation->paid_at ?: now();
            $attributes['receipt_number'] = $donation->receipt_number
                ?: 'COU-RCP-'.now()->format('Ymd').'-'.str_pad((string) $donation->id, 7, '0', STR_PAD_LEFT);
        }

        $donation->forceFill($attributes)->save();

        return $donation->fresh() ?? $donation;
    }

    public function normaliseStatus(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'PAID', 'SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'TS' => 'successful',
            'FAILED', 'REJECTED', 'TF' => 'failed',
            'CANCELLED', 'CANCELED' => 'cancelled',
            'REFUNDED' => 'refunded',
            default => 'pending',
        };
    }

    private function isSuccessful(string $status): bool
    {
        return in_array($status, ['successful', 'paid', 'completed'], true);
    }
}
