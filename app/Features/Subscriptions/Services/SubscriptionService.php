<?php

namespace App\Features\Subscriptions\Services;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Features\Groups\Models\Group;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;

/**
 * Domain service for Subscription.
 */
class SubscriptionService
{
    /**
     * Create trial.
     */
    public function createTrial(Group $group, SubscriptionPlan $plan): Subscription
    {
        $startDate = now();
        $endDate = $startDate->copy()->addDays($plan->trial_days);

        return Subscription::create([
            'group_id' => $group->id,
            'subscription_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => SubscriptionStatus::Trial,
        ]);
    }

    /**
     * Renew.
     */
    public function renew(Group $group, SubscriptionPlan $plan): Subscription
    {
        $startDate = now();
        $endDate = $plan->billing_cycle === BillingCycle::Annual
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();

        return Subscription::create([
            'group_id' => $group->id,
            'subscription_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => SubscriptionStatus::Active,
        ]);
    }

    /**
     * Check expiry.
     */
    public function checkExpiry(): int
    {
        $count = 0;

        Subscription::query()
            ->whereIn('status', [SubscriptionStatus::Trial, SubscriptionStatus::Active])
            ->where('end_date', '<', Carbon::today())
            ->each(function (Subscription $subscription) use (&$count): void {
                $subscription->update(['status' => SubscriptionStatus::Expired]);
                $count++;
            });

        return $count;
    }

    /**
     * Can add member.
     */
    public function canAddMember(Group $group): bool
    {
        $subscription = $group->activeSubscription;
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan) {
            return false;
        }

        $memberCount = $group->members()->count();

        return $memberCount < $plan->max_members;
    }
}
