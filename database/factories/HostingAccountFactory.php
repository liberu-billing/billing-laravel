<?php

namespace Database\Factories;

use App\Models\HostingAccount;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\HostingServer;
use Illuminate\Database\Eloquent\Factories\Factory;

class HostingAccountFactory extends Factory
{
    protected $model = HostingAccount::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subscription_id' => Subscription::factory(),
            'hosting_server_id' => HostingServer::factory(),
            'control_panel' => fake()->randomElement(['cpanel', 'plesk', 'directadmin', 'virtualmin', 'liberu']),
            'username' => fake()->userName(),
            'domain' => fake()->domainName(),
            'package' => fake()->randomElement(['basic', 'standard', 'premium', 'business']),
            'status' => 'active',
            'price' => fake()->randomFloat(2, 5, 100),
            'addons' => [],
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function suspended(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function terminated(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function withAddons(array $addons): self
    {
        return $this->state(fn (array $attributes) => [
            'addons' => $addons,
        ]);
    }

    public function cpanel(): self
    {
        return $this->state(fn (array $attributes) => [
            'control_panel' => 'cpanel',
        ]);
    }

    public function plesk(): self
    {
        return $this->state(fn (array $attributes) => [
            'control_panel' => 'plesk',
        ]);
    }

    public function directadmin(): self
    {
        return $this->state(fn (array $attributes) => [
            'control_panel' => 'directadmin',
        ]);
    }

    public function virtualmin(): self
    {
        return $this->state(fn (array $attributes) => [
            'control_panel' => 'virtualmin',
        ]);
    }

    public function liberu(): self
    {
        return $this->state(fn (array $attributes) => [
            'control_panel' => 'liberu',
        ]);
    }
}
