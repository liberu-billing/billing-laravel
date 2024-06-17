<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Products_Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'product_service_id' => Products_Service::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->optional()->date(),
            'renewal_period' => $this->faker->randomElement(['monthly', 'yearly']),
            'status' => $this->faker->randomElement(['active', 'cancelled', 'expired']),
        ];
    }
}
