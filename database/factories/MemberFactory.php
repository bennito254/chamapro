<?php

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Features\Groups\Models\Group;
use App\Features\Members\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'membership_number' => fake()->unique()->numerify('M####'),
            'full_name' => fake()->name(),
            'id_number' => fake()->numerify('########'),
            'phone_number' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_joined' => now()->subMonths(rand(1, 24)),
            'address' => fake()->address(),
            'occupation' => fake()->jobTitle(),
            'next_of_kin' => fake()->name(),
            'next_of_kin_phone' => fake()->phoneNumber(),
            'status' => MemberStatus::Active,
        ];
    }
}
