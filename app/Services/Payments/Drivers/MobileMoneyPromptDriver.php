<?php
namespace App\Services\Payments\Drivers;
use App\Models\{Donation,PaymentGateway};
use App\Services\Payments\Contracts\PaymentDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MobileMoneyPromptDriver implements PaymentDriver
{
    public function initialize(Donation $donation, PaymentGateway $gateway): array
    {
        $settings=$gateway->settings??[]; $credentials=$gateway->credentials??[];
        $url=(string)Arr::get($settings,'initialize_url','');
        if($url==='') throw new RuntimeException('Mobile Money initialize URL is not configured.');
        if(blank($donation->donor_phone)) throw new RuntimeException('A mobile money phone number is required.');
        $response=Http::timeout((int)Arr::get($settings,'timeout',30))
            ->withHeaders(array_filter(['Authorization'=>Arr::get($credentials,'api_key')?'Bearer '.Arr::get($credentials,'api_key'):null,'X-API-Secret'=>Arr::get($credentials,'api_secret'),'Accept'=>'application/json']))
            ->post($url,['reference'=>$donation->reference,'amount'=>(float)$donation->amount,'currency'=>$donation->currency,'phone'=>$donation->donor_phone,'callback_url'=>Arr::get($settings,'callback_url')]);
        if(!$response->successful()) throw new RuntimeException('Mobile Money prompt could not be sent.');
        return ['reference'=>$donation->reference,'status'=>'pending','provider'=>$gateway->provider,'message'=>'Approve the payment prompt on your phone.','provider_response'=>$response->json()];
    }
}
