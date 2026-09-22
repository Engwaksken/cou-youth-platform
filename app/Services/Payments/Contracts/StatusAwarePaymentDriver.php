<?php

declare(strict_types=1);

namespace App\Services\Payments\Contracts;

use App\Models\Donation;
use App\Models\PaymentGateway;

interface StatusAwarePaymentDriver extends PaymentDriver
{
    public function queryStatus(Donation $donation, PaymentGateway $gateway): array;
}
