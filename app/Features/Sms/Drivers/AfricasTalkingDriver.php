<?php

declare(strict_types=1);

namespace App\Features\Sms\Drivers;

use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway driver implementation for Africas Talking.
 */
class AfricasTalkingDriver implements SmsDriver
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function __construct(private array $credentials) {}

    /**
     * Send.
     */
    public function send(string $recipient, string $body): SmsSendResult
    {
        $apiKey = (string) ($this->credentials['api_key'] ?? '');
        $username = (string) ($this->credentials['username'] ?? '');
        $from = (string) ($this->credentials['from'] ?? '');

        if ($apiKey === '' || $username === '') {
            return new SmsSendResult(success: false, error: 'Africa\'s Talking credentials are incomplete.');
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $username,
                'to' => $recipient,
                'message' => $body,
                'from' => $from,
            ]);

            if ($response->successful()) {
                $messageId = $response->json('SMSMessageData.Recipients.0.messageId');

                return new SmsSendResult(success: true, messageId: is_string($messageId) ? $messageId : null);
            }

            return new SmsSendResult(success: false, error: $response->body());
        } catch (\Throwable $e) {
            Log::error('Africa\'s Talking SMS failed', ['error' => $e->getMessage()]);

            return new SmsSendResult(success: false, error: $e->getMessage());
        }
    }
}
