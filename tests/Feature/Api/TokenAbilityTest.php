<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\TokenAbility;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TokenAbilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass model policies so the only 403 source under test is the ability gate.
        Gate::before(fn (): true => true);
    }

    private function userWithPassword(): User
    {
        return User::factory()->withPersonalTeam()->create([
            'password' => bcrypt('password'),
        ]);
    }

    public function test_token_request_with_specific_abilities_grants_only_those(): void
    {
        $user = $this->userWithPassword();

        $response = $this->postJson('/api/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
            'abilities' => ['invoices:read'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('abilities', ['invoices:read']);

        $this->assertNotContains('*', $response->json('abilities'));
    }

    public function test_token_request_without_abilities_grants_full_taxonomy(): void
    {
        $user = $this->userWithPassword();

        $response = $this->postJson('/api/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200);

        $granted = $response->json('abilities');
        $this->assertEqualsCanonicalizing(TokenAbility::values(), $granted);
        $this->assertNotContains('*', $granted);
    }

    public function test_token_request_with_invalid_ability_is_rejected(): void
    {
        $user = $this->userWithPassword();

        $response = $this->postJson('/api/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
            'abilities' => ['invoices:read', 'not-a-real-ability'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['abilities.1']);
    }

    public function test_read_ability_allows_read_but_blocks_write(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Sanctum::actingAs($user, ['invoices:read']);

        $this->getJson('/api/invoices')->assertStatus(200);

        $customer = Customer::factory()->create();
        $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'price' => 100.00],
            ],
        ])->assertStatus(403);
    }

    public function test_write_ability_is_not_blocked_by_ability_gate(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Sanctum::actingAs($user, ['invoices:write']);

        $customer = Customer::factory()->create();
        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'price' => 100.00],
            ],
        ]);

        $response->assertStatus(201);
    }
}
