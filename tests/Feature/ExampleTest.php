<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the root route ("/") returns a successful response.
     */
    public function test_the_root_route_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the "/app" route redirects to login when unauthenticated.
     */
    public function test_the_app_route_returns_a_successful_response(): void
    {
        $response = $this->get('/app');
        $response->assertRedirect();
    }

    /**
     * Test the "/admin" route redirects to login when unauthenticated.
     */
    public function test_the_admin_route_returns_a_successful_response(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect();
    }
}
