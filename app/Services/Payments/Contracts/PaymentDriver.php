<?php
namespace App\Services\Payments\Contracts;
use App\Models\{Donation,PaymentGateway};
interface PaymentDriver { public function initialize(Donation $donation, PaymentGateway $gateway): array; }
