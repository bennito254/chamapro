<?php

declare(strict_types=1);

namespace App\Features\Sms\Contracts;

use App\Features\Sms\DTOs\SmsSendResult;

/**
 * Gateway driver implementation for Sms.
 */
interface SmsDriver
{
    /**
     * Send.
     *
     * @return SmsSendResult;
     */
    public function send(string $recipient, string $body): SmsSendResult;
}
