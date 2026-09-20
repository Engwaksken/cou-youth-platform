<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\StatusAwarePaymentDriver;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class AirtelMoneyDriver implements StatusAwarePaymentDriver
{
    public function initialize(Donation $donation, PaymentGateway $gateway): array
    {
        $settings = $gateway->settings ?? [];
        $credentials = $gateway->credentials ?? [];
        $baseUrl = $this->baseUrl($gateway, $settings);
        $clientId = $this->required($credentials, 'client_id', 'Airtel Client ID');
        $clientSecret = $this->required($credentials, 'client_secret', 'Airtel Client Secret');
        $country = strtoupper((string) Arr::get($settings, 'country', 'UG'));

        if (blank($donation->donor_phone)) {
            throw new RuntimeException('An Airtel Money phone number is required.');
        }

        $accessToken = $this->accessToken($baseUrl, $clientId, $clientSecret, $settings);

        $response = $this->client($settings)
            ->withToken($accessToken)
            ->withHeaders([
                'X-Country' => $country,
                'X-Currency' => strtoupper((string) $donation->currency),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($baseUrl.'/merchant/v1/payments/', [
                'reference' => $donation->reference,
                'subscriber' => [
                    'country' => $country,
                    'currency' => strtoupper((string) $donation->currency),
                    'msisdn' => $this->normalisePhone((string) $donation->donor_phone),
                ],
                'transaction' => [
                    'amount' => (float) $donation->amount,
                    'country' => $country,
                    'currency' => strtoupper((string) $donation->currency),
                    'id' => $donation->reference,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Airtel Money payment request was rejected: '.$response->body());
        }

        return [
            'reference' => $donation->reference,
            'status' => 'pending',
            'provider' => 'airtel_money',
            'message' => 'Approve the Airtel Money payment prompt on your phone.',
            'provider_response' => $response->json(),
        ];
    }

    public function queryStatus(Donation $donation, PaymentGateway $gateway): array
    {
        $settings = $gateway->settings ?? [];
        $credentials = $gateway->credentials ?? [];
        $baseUrl = $this->baseUrl($gateway, $settings);
        $clientId = $this->required($credentials, 'client_id', 'Airtel Client ID');
        $clientSecret = $this->required($credentials, 'client_secret', 'Airtel Client Secret');
        $country = strtoupper((string) Arr::get($settings, 'country', 'UG'));
        $accessToken = $this->accessToken($baseUrl, $clientId, $clientSecret, $settings);

        $response = $this->client($settings)
            ->withToken($accessToken)
            ->withHeaders([
                'X-Country' => $country,
                'X-Currency' => strtoupper((string) $donation->currency),
                'Accept' => 'application/json',
            ])
            ->get($baseUrl.'/standard/v1/payments/'.$donation->reference);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to query Airtel Money payment status: '.$response->body());
        }

        $payload = $response->json();
        $status = (string) (
            Arr::get($payload, 'data.transaction.status')
            ?? Arr::get($payload, 'transaction.status')
            ?? Arr::get($payload, 'status')
            ?? 'PENDING'
        );

        $transactionId = Arr::get($payload, 'data.transaction.airtel_money_id')
            ?? Arr::get($payload, 'data.transaction.id')
            ?? Arr::get($payload, 'transaction.id');

        return [
            'status' => $this->mapStatus($status),
            'transaction_id' => $transactionId,
            'provider_response' => $payload,
        ];
    }

    private function accessToken(string $baseUrl, string $clientId, string $clientSecret, array $settings): string
    {
        $response = $this->client($settings)
            ->acceptJson()
            ->post($baseUrl.'/auth/oauth2/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to authenticate with Airtel Money: '.$response->body());
        }

        $token = (string) Arr::get($response->json(), 'access_token', '');
        if ($token === '') {
            throw new RuntimeException('Airtel Money did not return an access token.');
        }

        return $token;
    }

    private function client(array $settings): PendingRequest
    {
        return Http::timeout((int) Arr::get($settings, 'timeout', 30));
    }

    private function baseUrl(PaymentGateway $gateway, array $settings): string
    {
        $configured = rtrim((string) Arr::get($settings, 'base_url', ''), '/');
        if ($configured !== '') {
            return $configured;
        }

        if ($gateway->is_test_mode) {
            return 'https://openapiuat.airtel.africa';
        }

        return 'https://openapi.airtel.africa';
    }

    private function required(array $credentials, string $key, string $label): string
    {
        $value = trim((string) Arr::get($credentials, $key, ''));
        if ($value === '') {
            throw new RuntimeException($label.' is not configured.');
        }

        return $value;
    }

    private function normalisePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: $phone;
    }

    private function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'TS', 'SUCCESS', 'SUCCESSFUL', 'COMPLETED' => 'successful',
            'TF', 'FAILED', 'REJECTED' => 'failed',
            'CANCELLED', 'CANCELED' => 'cancelled',
            default => 'pending',
        };
    }
}
