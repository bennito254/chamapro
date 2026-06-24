<?php

declare(strict_types=1);

namespace App\Features\Admin\Services;

use App\Features\Groups\Models\Group;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Services\SmsGatewayManager;
use App\Features\Subscriptions\Models\Subscription;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Sends platform SMS messages to group owners filtered by subscription status.
 */
class AdminOwnerSmsService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupOwnerService $groupOwnerService,
        private SmsGatewayManager $gateway,
    ) {}

    /**
     * @return Collection<int, Group>
     */
    public function groupsForStatus(string $status): Collection
    {
        $subscriptions = Subscription::query()
            ->with('group')
            ->latest()
            ->get()
            ->unique('group_id')
            ->values();

        if ($status !== 'all') {
            $subscriptions = $subscriptions
                ->filter(fn (Subscription $subscription): bool => $subscription->status->value === $status)
                ->values();
        }

        return $subscriptions
            ->map(fn (Subscription $subscription): ?Group => $subscription->group)
            ->filter()
            ->values();
    }

    /**
     * Count groups with a phone number available for SMS.
     */
    public function recipientCount(string $status): int
    {
        return $this->groupsForStatus($status)
            ->filter(fn (Group $group): bool => filled($group->phone))
            ->count();
    }

    /**
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function send(string $status, string $body): array
    {
        $groups = $this->groupsForStatus($status);
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($groups as $group) {
            $owner = $this->groupOwnerService->resolve($group);

            if (! filled($owner['phone'])) {
                $skipped++;

                continue;
            }

            $message = $this->gateway->sendForGroup(
                groupId: $group->id,
                recipient: (string) $owner['phone'],
                body: $body,
            );

            if ($message->status === 'sent') {
                $sent++;
            } else {
                $failed++;
            }
        }

        if ($groups->isEmpty()) {
            throw new InvalidArgumentException('No groups matched the selected subscription status.');
        }

        return compact('sent', 'skipped', 'failed');
    }

    /**
     * @return Collection<int, SmsMessage>
     */
    public function recentMessages(int $limit = 20): Collection
    {
        return SmsMessage::query()
            ->withoutGlobalScopes()
            ->whereNull('member_id')
            ->whereNull('sms_template_id')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
