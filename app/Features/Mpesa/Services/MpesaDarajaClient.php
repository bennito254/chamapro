<?php

declare(strict_types=1);

namespace App\Features\Mpesa\Services;

use App\Features\Admin\Services\PlatformMpesaSettingsService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Safaricom Daraja API client for M-Pesa Express (STK Push).
 */
class MpesaDarajaClient
{
    public function __construct(
        private PlatformMpesaSettingsService $settings,
    ) {}

    /**
     * @return array{checkout_request_id: string, merchant_request_id: ?string, response: array<string, mixed>}
     */
    public function stkPush(string $phoneNumber, float $amount, string $accountReference, string $description): array
    {
        $settings = $this->settings->all();
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($settings[PlatformMpesaSettingsService::KEY_SHORTCODE].$settings[PlatformMpesaSettingsService::KEY_PASSKEY].$timestamp);

        $payload = [
            'BusinessShortCode' => $settings[PlatformMpesaSettingsService::KEY_SHORTCODE],
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) ceil($amount),
            'PartyA' => $phoneNumber,
            'PartyB' => $settings[PlatformMpesaSettingsService::KEY_SHORTCODE],
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $settings[PlatformMpesaSettingsService::KEY_CALLBACK_URL],
            'AccountReference' => Str::limit($accountReference, 12, ''),
            'TransactionDesc' => Str::limit($description, 13, ''),
        ];

        $response = Http::withToken($this->accessToken())
            ->post($this->settings->baseUrl().'/mpesa/stkpush/v1/processrequest', $payload)
            ->throw()
            ->json();

        $checkoutRequestId = data_get($response, 'CheckoutRequestID');

        if (! is_string($checkoutRequestId) || $checkoutRequestId === '') {
            throw new RuntimeException('Daraja STK push did not return a CheckoutRequestID.');
        }

        return [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => data_get($response, 'MerchantRequestID'),
            'response' => is_array($response) ? $response : [],
        ];
    }

    private function accessToken(): string
    {
        $settings = $this->settings->all();

        try {
            $response = Http::withBasicAuth(
                $settings[PlatformMpesaSettingsService::KEY_CONSUMER_KEY],
                $settings[PlatformMpesaSettingsService::KEY_CONSUMER_SECRET],
            )
                ->get($this->settings->baseUrl().'/oauth/v1/generate', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException('Failed to obtain M-Pesa access token: '.$exception->getMessage(), 0, $exception);
        }

        $token = data_get($response, 'access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('M-Pesa access token response was invalid.');
        }

        return $token;
    }
}
