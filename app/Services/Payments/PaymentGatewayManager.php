<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentDriver;
use App\Services\Payments\Contracts\StatusAwarePaymentDriver;
use App\Services\Payments\Drivers\AirtelMoneyDriver;
use App\Services\Payments\Drivers\ConfigurableHttpDriver;
use App\Services\Payments\Drivers\MtnMomoDriver;
use App\Services\Payments\Drivers\MobileMoneyPromptDriver;
use RuntimeException;

final class PaymentGatewayManager
{
    public function initializeDonation(Donation $donation, PaymentGateway $gateway): array
    {
        if (! $gateway->is_enabled) {
            throw new RuntimeException('Selected payment method is unavailable.');
        }

        return $this->driver($gateway)->initialize($donation, $gateway);
    }

    public function queryDonationStatus(Donation $donation, PaymentGateway $gateway): array
    {
        if (! $gateway->is_enabled) {
            throw new RuntimeException('Selected payment method is unavailable.');
        }

        $driver = $this->driver($gateway);

        if (! $driver instanceof StatusAwarePaymentDriver) {
            throw new RuntimeException('This payment provider does not support transaction status polling.');
        }

        return $driver->queryStatus($donation, $gateway);
    }

    private function driver(PaymentGateway $gateway): PaymentDriver
    {
        return match (strtolower((string) $gateway->provider)) {
            'mtn_momo', 'mtn', 'mtn_mobile_money' => app(MtnMomoDriver::class),
            'airtel_money', 'airtel', 'airtel_momo' => app(AirtelMoneyDriver::class),
            'mobile_money' => app(MobileMoneyPromptDriver::class),
            'flutterwave', 'pesapal', 'stripe', 'paypal', 'http' => app(ConfigurableHttpDriver::class),
            default => app(ConfigurableHttpDriver::class),
        };
    }
}
