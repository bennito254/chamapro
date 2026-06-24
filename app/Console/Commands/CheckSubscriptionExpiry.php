<?php

namespace App\Console\Commands;

use App\Features\Subscriptions\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Application class for check subscription expiry.
 */
class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';

    protected $description = 'Mark expired subscriptions and notify groups';

    /**
     * Handle.
     */
    public function handle(SubscriptionService $subscriptionService): int
    {
        $count = $subscriptionService->checkExpiry();

        $this->info("Marked {$count} subscription(s) as expired.");

        return self::SUCCESS;
    }
}
