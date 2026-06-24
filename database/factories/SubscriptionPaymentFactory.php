<?php

namespace Database\Factories;

use App\Enums\SubscriptionPaymentStatus;
use App\Features\Groups\Models\Group;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    protected $model = SubscriptionPayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'subscription_plan_id' => 1,
            'phone_number' => '2547'.fake()->numerify('########'),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'status' => SubscriptionPaymentStatus::Pending,
            'checkout_request_id' => 'CHK'.strtoupper(fake()->bothify('????????????')),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionPaymentStatus::Completed,
            'paid_at' => now(),
            'mpesa_receipt_number' => fake()->numerify('##########'),
        ]);
    }
}
