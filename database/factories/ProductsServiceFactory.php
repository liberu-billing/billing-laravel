<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products_Service>
 */
class ProductsServiceFactory extends Factory
{
    protected $model = \App\Models\Products_Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'base_price' => $this->faker->randomFloat(2, 1, 1000),
            'type' => $this->faker->randomElement(['product', 'service', 'hosting']),
        ];
    }
}
