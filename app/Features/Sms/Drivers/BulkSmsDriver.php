<?php

declare(strict_types=1);

namespace App\Features\Sms\Drivers;

use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway driver implementation for Bulk Sms.
 */
class BulkSmsDriver implements SmsDriver
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
        $token = (string) ($this->credentials['token'] ?? '');
        $endpoint = (string) ($this->credentials['endpoint'] ?? 'https://api.bulksms.com/v1/messages');

        if ($token === '') {
            return new SmsSendResult(success: false, error: 'BulkSMS credentials are incomplete.');
        }

        try {
            $response = Http::withToken($token)->post($endpoint, [
                'to' => $recipient,
                'body' => $body,
            ]);

            if ($response->successful()) {
                $messageId = $response->json('id') ?? $response->json('message_id');

                return new SmsSendResult(success: true, messageId: is_string($messageId) ? $messageId : null);
            }

            return new SmsSendResult(success: false, error: $response->body());
        } catch (\Throwable $e) {
            Log::error('BulkSMS failed', ['error' => $e->getMessage()]);

            return new SmsSendResult(success: false, error: $e->getMessage());
        }
    }
}
