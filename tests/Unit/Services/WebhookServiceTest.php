<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\WebhookService;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WebhookService $webhookService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookService = new WebhookService();
    }

    public function test_can_dispatch_webhook_event()
    {
        $endpoint = WebhookEndpoint::create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => [WebhookService::EVENT_INVOICE_CREATED],
        ]);

        $this->webhookService->dispatch(
            WebhookService::EVENT_INVOICE_CREATED,
            ['invoice_id' => 1, 'amount' => 100]
        );

        $this->assertDatabaseHas('webhook_events', [
            'webhook_endpoint_id' => $endpoint->id,
            'event_type' => WebhookService::EVENT_INVOICE_CREATED,
            'status' => 'pending',
        ]);
    }

    public function test_webhook_endpoint_subscription_filter()
    {
        $endpoint = WebhookEndpoint::create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => [WebhookService::EVENT_INVOICE_CREATED],
        ]);

        $this->assertTrue($endpoint->isSubscribedTo(WebhookService::EVENT_INVOICE_CREATED));
        $this->assertFalse($endpoint->isSubscribedTo(WebhookService::EVENT_PAYMENT_RECEIVED));
    }

    public function test_inactive_endpoint_not_subscribed()
    {
        $endpoint = WebhookEndpoint::create([
            'url' => 'https://example.com/webhook',
            'is_active' => false,
            'events' => [WebhookService::EVENT_INVOICE_CREATED],
        ]);

        $this->assertFalse($endpoint->isSubscribedTo(WebhookService::EVENT_INVOICE_CREATED));
    }

    public function test_webhook_signature_verification()
    {
        $payload = json_encode(['test' => 'data']);
        $secret = 'test-secret';
        $signature = hash_hmac('sha256', $payload, $secret);

        $this->assertTrue(
            WebhookService::verifySignature($payload, $signature, $secret)
        );

        $this->assertFalse(
            WebhookService::verifySignature($payload, 'invalid-signature', $secret)
        );
    }

    public function test_get_available_events()
    {
        $events = WebhookService::getAvailableEvents();

        $this->assertIsArray($events);
        $this->assertArrayHasKey(WebhookService::EVENT_INVOICE_CREATED, $events);
        $this->assertArrayHasKey(WebhookService::EVENT_PAYMENT_RECEIVED, $events);
    }
}
