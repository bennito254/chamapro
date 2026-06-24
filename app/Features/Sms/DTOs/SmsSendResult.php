<?php

declare(strict_types=1);

namespace App\Features\Sms\DTOs;

/**
 * Result of an outbound SMS send attempt.
 */
readonly class SmsSendResult
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public bool $success,
        public ?string $messageId = null,
        public ?string $error = null,
    ) {}
}
