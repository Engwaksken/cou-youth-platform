<?php
namespace App\Services\Payments;
use App\Models\{Donation,PaymentGateway};
use App\Services\Payments\Contracts\PaymentDriver;
use App\Services\Payments\Drivers\{ConfigurableHttpDriver,MobileMoneyPromptDriver};
use RuntimeException;

class PaymentGatewayManager
{
    public function initializeDonation(Donation $donation, PaymentGateway $gateway): array
    {
        if(!$gateway->is_enabled) throw new RuntimeException('Selected payment method is unavailable.');
        return $this->driver($gateway)->initialize($donation,$gateway);
    }
    private function driver(PaymentGateway $gateway): PaymentDriver
    {
        return match(strtolower($gateway->provider)) {
            'mtn_momo','airtel_money','mobile_money' => app(MobileMoneyPromptDriver::class),
            'flutterwave','pesapal','stripe','paypal','http' => app(ConfigurableHttpDriver::class),
            default => app(ConfigurableHttpDriver::class),
        };
    }
}
