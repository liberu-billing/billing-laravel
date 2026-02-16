<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CannedResponseService;
use App\Models\CannedResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CannedResponseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CannedResponseService $cannedResponseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cannedResponseService = new CannedResponseService();
    }

    public function test_can_get_canned_response_by_shortcode()
    {
        CannedResponse::create([
            'title' => 'Welcome Message',
            'shortcode' => 'welcome',
            'content' => 'Welcome {{client_name}}!',
            'is_active' => true,
        ]);

        $response = $this->cannedResponseService->getByShortcode('welcome');

        $this->assertNotNull($response);
        $this->assertEquals('Welcome Message', $response->title);
    }

    public function test_can_replace_variables_in_response()
    {
        $response = CannedResponse::create([
            'title' => 'Welcome Message',
            'shortcode' => 'welcome',
            'content' => 'Hello {{client_name}}, your ticket number is {{ticket_id}}.',
            'is_active' => true,
        ]);

        $content = $this->cannedResponseService->use($response, [
            'client_name' => 'John Doe',
            'ticket_id' => '12345',
        ]);

        $this->assertEquals('Hello John Doe, your ticket number is 12345.', $content);
    }

    public function test_usage_count_increments()
    {
        $response = CannedResponse::create([
            'title' => 'Test Response',
            'shortcode' => 'test',
            'content' => 'Test content',
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $this->cannedResponseService->use($response);

        $this->assertEquals(1, $response->fresh()->usage_count);
        $this->assertNotNull($response->fresh()->last_used_at);
    }

    public function test_can_search_canned_responses()
    {
        CannedResponse::create([
            'title' => 'Password Reset',
            'shortcode' => 'pwd-reset',
            'content' => 'Instructions for resetting password',
            'is_active' => true,
        ]);

        CannedResponse::create([
            'title' => 'Account Setup',
            'shortcode' => 'account',
            'content' => 'Welcome to your account',
            'is_active' => true,
        ]);

        $results = $this->cannedResponseService->search('password');

        $this->assertCount(1, $results);
        $this->assertEquals('Password Reset', $results->first()->title);
    }

    public function test_can_get_most_used_responses()
    {
        CannedResponse::create([
            'title' => 'Popular Response',
            'shortcode' => 'popular',
            'content' => 'Popular content',
            'is_active' => true,
            'usage_count' => 100,
        ]);

        CannedResponse::create([
            'title' => 'Unpopular Response',
            'shortcode' => 'unpopular',
            'content' => 'Unpopular content',
            'is_active' => true,
            'usage_count' => 5,
        ]);

        $mostUsed = $this->cannedResponseService->getMostUsed(1);

        $this->assertCount(1, $mostUsed);
        $this->assertEquals('Popular Response', $mostUsed->first()->title);
    }

    public function test_available_variables()
    {
        $variables = CannedResponseService::getAvailableVariables();

        $this->assertIsArray($variables);
        $this->assertArrayHasKey('client_name', $variables);
        $this->assertArrayHasKey('ticket_id', $variables);
        $this->assertArrayHasKey('invoice_number', $variables);
    }
}
