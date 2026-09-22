<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Models\Donation;
use App\Services\Payments\DonationStatusUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DonationStatusUpdaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_status_creates_receipt_and_payment_timestamp(): void
    {
        $donation = $this->donation('pending');

        $updated = app(DonationStatusUpdater::class)->apply(
            $donation,
            'SUCCESSFUL',
            'TX-123',
            ['status' => 'SUCCESSFUL'],
        );

        $this->assertSame('successful', $updated->status);
        $this->assertSame('TX-123', $updated->external_transaction_id);
        $this->assertNotNull($updated->paid_at);
        $this->assertNotNull($updated->receipt_number);
        $this->assertStringStartsWith('COU-RCP-', (string) $updated->receipt_number);
    }

    public function test_successful_payment_cannot_be_downgraded_by_late_pending_status(): void
    {
        $donation = $this->donation('successful');
        $donation->forceFill([
            'receipt_number' => 'COU-RCP-EXISTING',
            'paid_at' => now(),
        ])->save();

        $updated = app(DonationStatusUpdater::class)->apply(
            $donation,
            'pending',
            null,
            ['status' => 'PENDING'],
        );

        $this->assertSame('successful', $updated->status);
        $this->assertSame('COU-RCP-EXISTING', $updated->receipt_number);
    }

    public function test_failed_payment_does_not_regress_to_pending_but_can_later_succeed(): void
    {
        $donation = $this->donation('failed');
        $updater = app(DonationStatusUpdater::class);

        $stillFailed = $updater->apply($donation, 'pending');
        $this->assertSame('failed', $stillFailed->status);

        $successful = $updater->apply($stillFailed, 'successful', 'TX-RECOVERED');
        $this->assertSame('successful', $successful->status);
        $this->assertSame('TX-RECOVERED', $successful->external_transaction_id);
    }

    private function donation(string $status): Donation
    {
        return Donation::create([
            'reference' => (string) Str::uuid(),
            'amount' => 10000,
            'currency' => 'UGX',
            'status' => $status,
            'is_anonymous' => false,
        ]);
    }
}
