<?php

declare(strict_types=1);

namespace App\Features\Sms\Drivers;

use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway driver implementation for Http.
 */
class HttpDriver implements SmsDriver
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
        $url = (string) ($this->credentials['url'] ?? '');
        $method = strtoupper((string) ($this->credentials['method'] ?? 'POST'));

        if ($url === '') {
            return new SmsSendResult(success: false, error: 'HTTP SMS gateway URL is not configured.');
        }

        $payload = [
            'recipient' => $recipient,
            'body' => $body,
            ...($this->credentials['payload'] ?? []),
        ];

        try {
            $request = Http::withHeaders($this->credentials['headers'] ?? []);

            $response = match ($method) {
                'GET' => $request->get($url, $payload),
                default => $request->post($url, $payload),
            };

            if ($response->successful()) {
                return new SmsSendResult(success: true, messageId: $response->json('message_id'));
            }

            return new SmsSendResult(success: false, error: $response->body());
        } catch (\Throwable $e) {
            Log::error('HTTP SMS gateway failed', ['error' => $e->getMessage()]);

            return new SmsSendResult(success: false, error: $e->getMessage());
        }
    }
}
