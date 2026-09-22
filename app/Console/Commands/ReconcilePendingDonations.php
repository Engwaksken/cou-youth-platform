<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Donation;
use App\Services\Payments\DonationStatusUpdater;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ReconcilePendingDonations extends Command
{
    protected $signature = 'payments:reconcile-pending {--limit=100 : Maximum pending donations to check} {--hours=168 : Only check donations created within this many hours}';

    protected $description = 'Reconcile pending MTN MoMo and Airtel Money donations with provider status APIs';

    public function handle(
        PaymentGatewayManager $payments,
        DonationStatusUpdater $statusUpdater,
    ): int {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $hours = max(1, min(24 * 30, (int) $this->option('hours')));
        $providers = [
            'mtn_momo', 'mtn', 'mtn_mobile_money',
            'airtel_money', 'airtel', 'airtel_momo',
        ];

        $donations = Donation::query()
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereHas('gateway', fn ($query) => $query
                ->where('is_enabled', true)
                ->whereIn('provider', $providers))
            ->with('gateway')
            ->oldest('created_at')
            ->limit($limit)
            ->get();

        $confirmed = 0;
        $updated = 0;
        $unchanged = 0;
        $failed = 0;

        foreach ($donations as $donation) {
            $gateway = $donation->gateway;
            if (! $gateway) {
                $failed++;
                continue;
            }

            try {
                $providerStatus = $payments->queryDonationStatus($donation, $gateway);
                $before = (string) $donation->status;

                $refreshed = $statusUpdater->apply(
                    $donation,
                    (string) ($providerStatus['status'] ?? 'pending'),
                    $providerStatus['transaction_id'] ?? null,
                    is_array($providerStatus['provider_response'] ?? null)
                        ? $providerStatus['provider_response']
                        : null,
                );

                if ($refreshed->status === 'successful') {
                    $confirmed++;
                } elseif ((string) $refreshed->status !== $before) {
                    $updated++;
                } else {
                    $unchanged++;
                }
            } catch (Throwable $exception) {
                $failed++;
                Log::warning('Pending donation reconciliation failed.', [
                    'donation_id' => $donation->id,
                    'gateway_id' => $gateway->id,
                    'provider' => $gateway->provider,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info(sprintf(
            'Checked %d pending donations: %d confirmed, %d updated, %d unchanged, %d errors.',
            $donations->count(),
            $confirmed,
            $updated,
            $unchanged,
            $failed,
        ));

        return self::SUCCESS;
    }
}
