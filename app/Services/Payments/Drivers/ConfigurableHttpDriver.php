<?php
namespace App\Services\Payments\Drivers;
use App\Models\{Donation,PaymentGateway};
use App\Services\Payments\Contracts\PaymentDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ConfigurableHttpDriver implements PaymentDriver
{
    public function initialize(Donation $donation, PaymentGateway $gateway): array
    {
        $settings = $gateway->settings ?? [];
        $credentials = $gateway->credentials ?? [];
        $url = (string) Arr::get($settings,'initialize_url','');
        if ($url === '') throw new RuntimeException('Payment provider initialize URL is not configured.');

        $headers = array_filter([
            'Authorization' => Arr::get($credentials,'api_key') ? 'Bearer '.Arr::get($credentials,'api_key') : null,
            'X-API-Secret' => Arr::get($credentials,'api_secret'),
            'Accept' => 'application/json',
        ]);

        $payload = [
            'reference'=>$donation->reference,
            'amount'=>(float)$donation->amount,
            'currency'=>$donation->currency,
            'callback_url'=>Arr::get($settings,'callback_url'),
            'webhook_url'=>Arr::get($settings,'webhook_url'),
            'customer'=>[
                'name'=>$donation->donor_name,
                'email'=>$donation->donor_email,
                'phone'=>$donation->donor_phone,
            ],
            'metadata'=>['donation_id'=>$donation->id,'campaign_id'=>$donation->campaign_id],
        ];

        $response = Http::timeout((int)Arr::get($settings,'timeout',30))->withHeaders($headers)->post($url,$payload);
        if (! $response->successful()) throw new RuntimeException('Payment provider could not initialize the transaction.');
        $body = $response->json();
        return [
            'reference'=>$donation->reference,
            'status'=>'pending',
            'provider'=>$gateway->provider,
            'checkout_url'=>Arr::get($body,'checkout_url') ?? Arr::get($body,'data.checkout_url'),
            'provider_reference'=>Arr::get($body,'reference') ?? Arr::get($body,'data.reference'),
            'provider_response'=>$body,
        ];
    }
}
