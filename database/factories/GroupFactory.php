<?php

namespace Database\Factories;

use App\Features\Groups\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Chama',
            'registration_number' => fake()->unique()->numerify('REG-####'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'county' => fake()->city(),
            'constituency' => fake()->streetName(),
            'currency' => 'KES',
            'status' => 'active',
        ];
    }
}
