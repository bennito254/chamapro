<?php

declare(strict_types=1);

namespace App\Features\Sms\Services;

use App\Features\Members\Models\Member;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Domain service for Sms.
 */
class SmsService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private SmsGatewayManager $gateway,
        private SmsTemplateRenderer $renderer,
    ) {}

    /**
     * @param  Collection<int, Member>|array<int, Member>  $members
     * @return Collection<int, SmsMessage>
     */
    public function sendToMembers(SmsTemplate $template, Collection|array $members, User $sender): Collection
    {
        $members = $members instanceof Collection ? $members : collect($members);

        return $members->map(function (Member $member) use ($template, $sender) {
            if (! $member->phone_number) {
                throw new InvalidArgumentException("Member {$member->full_name} does not have a phone number.");
            }

            $body = $this->renderer->render($template, $member);

            return $this->gateway->send(
                recipient: $member->phone_number,
                body: $body,
                member: $member,
                template: $template,
                sender: $sender,
            );
        });
    }

    /**
     * Preview.
     */
    public function preview(SmsTemplate $template, Member $member): string
    {
        return $this->renderer->render($template, $member);
    }
}
