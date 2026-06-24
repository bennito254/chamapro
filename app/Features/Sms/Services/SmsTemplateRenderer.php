<?php

declare(strict_types=1);

namespace App\Features\Sms\Services;

use App\Features\Members\Models\Member;
use App\Features\Sms\Models\SmsTemplate;

/**
 * Renders structured output for the application for Sms Template.
 */
class SmsTemplateRenderer
{
    /**
     * Create a new instance.
     */
    public function __construct(private SmsPlaceholderResolver $placeholderResolver) {}

    /**
     * Render.
     */
    public function render(SmsTemplate $template, Member $member): string
    {
        return $this->renderBody($template->body, $member);
    }

    /**
     * Render body.
     */
    public function renderBody(string $body, Member $member): string
    {
        $placeholders = $this->placeholderResolver->resolve($member);

        return preg_replace_callback(
            '/\{([a-z_]+)\}/',
            fn (array $matches) => $placeholders[$matches[1]] ?? $matches[0],
            $body,
        ) ?? $body;
    }
}
