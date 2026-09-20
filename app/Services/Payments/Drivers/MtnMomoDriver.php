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

final class MtnMomoDriver implements StatusAwarePaymentDriver
{
    public function initialize(Donation $donation, PaymentGateway $gateway): array
    {
        $settings = $gateway->settings ?? [];
        $credentials = $gateway->credentials ?? [];
        $baseUrl = $this->baseUrl($gateway, $settings);
        $subscriptionKey = $this->required($credentials, 'subscription_key', 'MTN Collections subscription key');
        $apiUser = $this->required($credentials, 'api_user', 'MTN API User');
        $apiKey = $this->required($credentials, 'api_key', 'MTN API Key');
        $targetEnvironment = $this->targetEnvironment($gateway, $settings);

        if (blank($donation->donor_phone)) {
            throw new RuntimeException('A mobile money phone number is required.');
        }

        $accessToken = $this->accessToken($baseUrl, $subscriptionKey, $apiUser, $apiKey, $settings);

        $request = $this->client($settings)
            ->withToken($accessToken)
            ->withHeaders(array_filter([
                'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                'X-Reference-Id' => $donation->reference,
                'X-Target-Environment' => $targetEnvironment,
                'X-Callback-Url' => Arr::get($settings, 'callback_url'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]));

        $response = $request->post($baseUrl.'/collection/v1_0/requesttopay', [
            'amount' => number_format((float) $donation->amount, 2, '.', ''),
            'currency' => strtoupper((string) $donation->currency),
            'externalId' => $donation->reference,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $this->normalisePhone((string) $donation->donor_phone),
            ],
            'payerMessage' => 'Church of Uganda Youth Platform donation',
            'payeeNote' => 'Donation '.$donation->reference,
        ]);

        if ($response->status() !== 202) {
            throw new RuntimeException('MTN MoMo payment request was rejected: '.$response->body());
        }

        return [
            'reference' => $donation->reference,
            'status' => 'pending',
            'provider' => 'mtn_momo',
            'message' => 'Approve the MTN MoMo payment prompt on your phone.',
        ];
    }

    public function queryStatus(Donation $donation, PaymentGateway $gateway): array
    {
        $settings = $gateway->settings ?? [];
        $credentials = $gateway->credentials ?? [];
        $baseUrl = $this->baseUrl($gateway, $settings);
        $subscriptionKey = $this->required($credentials, 'subscription_key', 'MTN Collections subscription key');
        $apiUser = $this->required($credentials, 'api_user', 'MTN API User');
        $apiKey = $this->required($credentials, 'api_key', 'MTN API Key');
        $targetEnvironment = $this->targetEnvironment($gateway, $settings);
        $accessToken = $this->accessToken($baseUrl, $subscriptionKey, $apiUser, $apiKey, $settings);

        $response = $this->client($settings)
            ->withToken($accessToken)
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                'X-Target-Environment' => $targetEnvironment,
                'Accept' => 'application/json',
            ])
            ->get($baseUrl.'/collection/v1_0/requesttopay/'.$donation->reference);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to query MTN MoMo payment status: '.$response->body());
        }

        $payload = $response->json();

        return [
            'status' => $this->mapStatus((string) Arr::get($payload, 'status', 'PENDING')),
            'transaction_id' => Arr::get($payload, 'financialTransactionId'),
            'provider_response' => $payload,
        ];
    }

    private function accessToken(string $baseUrl, string $subscriptionKey, string $apiUser, string $apiKey, array $settings): string
    {
        $response = $this->client($settings)
            ->withBasicAuth($apiUser, $apiKey)
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                'Accept' => 'application/json',
            ])
            ->post($baseUrl.'/collection/token/');

        if (! $response->successful()) {
            throw new RuntimeException('Unable to authenticate with MTN MoMo: '.$response->body());
        }

        $token = (string) Arr::get($response->json(), 'access_token', '');
        if ($token === '') {
            throw new RuntimeException('MTN MoMo did not return an access token.');
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
            return 'https://sandbox.momodeveloper.mtn.com';
        }

        throw new RuntimeException('MTN production base URL is not configured.');
    }

    private function targetEnvironment(PaymentGateway $gateway, array $settings): string
    {
        $configured = trim((string) Arr::get($settings, 'target_environment', ''));
        if ($configured !== '') {
            return $configured;
        }

        if ($gateway->is_test_mode) {
            return 'sandbox';
        }

        throw new RuntimeException('MTN production target environment is not configured.');
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
            'SUCCESSFUL' => 'successful',
            'FAILED' => 'failed',
            'REJECTED' => 'failed',
            default => 'pending',
        };
    }
}
