<?php

declare(strict_types=1);

namespace App\Features\Sms\Drivers;

use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway driver implementation for Twilio.
 */
class TwilioDriver implements SmsDriver
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
        $sid = (string) ($this->credentials['account_sid'] ?? '');
        $token = (string) ($this->credentials['auth_token'] ?? '');
        $from = (string) ($this->credentials['from'] ?? '');

        if ($sid === '' || $token === '' || $from === '') {
            return new SmsSendResult(success: false, error: 'Twilio credentials are incomplete.');
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $recipient,
                    'From' => $from,
                    'Body' => $body,
                ]);

            if ($response->successful()) {
                $messageId = $response->json('sid');

                return new SmsSendResult(success: true, messageId: is_string($messageId) ? $messageId : null);
            }

            return new SmsSendResult(success: false, error: $response->body());
        } catch (\Throwable $e) {
            Log::error('Twilio SMS failed', ['error' => $e->getMessage()]);

            return new SmsSendResult(success: false, error: $e->getMessage());
        }
    }
}
