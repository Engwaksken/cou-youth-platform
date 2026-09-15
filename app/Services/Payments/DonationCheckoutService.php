<?php
namespace App\Services\Payments;
use App\Models\{Donation,PaymentGateway};
class DonationCheckoutService {
 public function __construct(private PaymentGatewayManager $manager){}
 public function start(Donation $donation, PaymentGateway $gateway): array {return $this->manager->initializeDonation($donation,$gateway);}
}
