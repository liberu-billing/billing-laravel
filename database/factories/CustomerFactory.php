<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail,
            'phone_number' => fake()->phoneNumber,
            'address' => fake()->address,
            'city' => fake()->city,
            'state' => fake()->state,
            'postal_code' => fake()->postcode,
            'country' => fake()->country,
        ];
    }
}
