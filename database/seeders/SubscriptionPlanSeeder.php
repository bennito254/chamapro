<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::firstOrCreate(
            ['name' => 'Starter Monthly'],
            [
                'billing_cycle' => BillingCycle::Monthly,
                'amount' => 2500,
                'discount_percentage' => 0,
                'max_members' => 50,
                'max_users' => 5,
                'trial_days' => 14,
                'status' => 'active',
            ],
        );

        SubscriptionPlan::firstOrCreate(
            ['name' => 'Professional Monthly'],
            [
                'billing_cycle' => BillingCycle::Monthly,
                'amount' => 5000,
                'discount_percentage' => 0,
                'max_members' => 200,
                'max_users' => 15,
                'trial_days' => 14,
                'status' => 'active',
            ],
        );

        SubscriptionPlan::firstOrCreate(
            ['name' => 'Professional Annual'],
            [
                'billing_cycle' => BillingCycle::Annual,
                'amount' => 50000,
                'discount_percentage' => 15,
                'max_members' => 200,
                'max_users' => 15,
                'trial_days' => 14,
                'status' => 'active',
            ],
        );
    }
}
