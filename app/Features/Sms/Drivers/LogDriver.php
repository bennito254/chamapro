<?php

declare(strict_types=1);

namespace App\Features\Sms\Drivers;

use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Gateway driver implementation for Log.
 */
class LogDriver implements SmsDriver
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function __construct(private array $credentials = []) {}

    /**
     * Send.
     */
    public function send(string $recipient, string $body): SmsSendResult
    {
        Log::channel('sms')->info('SMS sent', [
            'recipient' => $recipient,
            'body' => $body,
        ]);

        return new SmsSendResult(
            success: true,
            messageId: 'log-'.Str::uuid()->toString(),
        );
    }
}
