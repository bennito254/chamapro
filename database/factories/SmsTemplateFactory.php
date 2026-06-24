<?php

namespace Database\Factories;

use App\Features\Groups\Models\Group;
use App\Features\Sms\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsTemplate>
 */
class SmsTemplateFactory extends Factory
{
    protected $model = SmsTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'name' => fake()->words(3, true),
            'body' => 'Dear {name}, your loan balance is KES {loan_balance}. - {group_name}',
            'status' => 'active',
        ];
    }
}
