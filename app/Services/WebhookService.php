<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Available webhook event types
     */
    public const EVENT_INVOICE_CREATED = 'invoice.created';
    public const EVENT_INVOICE_UPDATED = 'invoice.updated';
    public const EVENT_INVOICE_PAID = 'invoice.paid';
    public const EVENT_INVOICE_OVERDUE = 'invoice.overdue';
    public const EVENT_PAYMENT_RECEIVED = 'payment.received';
    public const EVENT_PAYMENT_FAILED = 'payment.failed';
    public const EVENT_PAYMENT_REFUNDED = 'payment.refunded';
    public const EVENT_SUBSCRIPTION_CREATED = 'subscription.created';
    public const EVENT_SUBSCRIPTION_UPDATED = 'subscription.updated';
    public const EVENT_SUBSCRIPTION_CANCELLED = 'subscription.cancelled';
    public const EVENT_SUBSCRIPTION_RENEWED = 'subscription.renewed';
    public const EVENT_CLIENT_CREATED = 'client.created';
    public const EVENT_CLIENT_UPDATED = 'client.updated';
    public const EVENT_SERVICE_PROVISIONED = 'service.provisioned';
    public const EVENT_SERVICE_SUSPENDED = 'service.suspended';
    public const EVENT_SERVICE_TERMINATED = 'service.terminated';
    public const EVENT_TICKET_CREATED = 'ticket.created';
    public const EVENT_TICKET_UPDATED = 'ticket.updated';
    public const EVENT_TICKET_CLOSED = 'ticket.closed';

    /**
     * Dispatch a webhook event to all subscribed endpoints
     */
    public function dispatch(string $eventType, array $payload, ?int $teamId = null): void
    {
        $query = WebhookEndpoint::query()
            ->where('is_active', true);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $endpoints = $query->get();

        foreach ($endpoints as $endpoint) {
            if ($endpoint->isSubscribedTo($eventType)) {
                $this->createWebhookEvent($endpoint, $eventType, $payload);
            }
        }
    }

    /**
     * Create a webhook event record
     */
    protected function createWebhookEvent(WebhookEndpoint $endpoint, string $eventType, array $payload): WebhookEvent
    {
        return WebhookEvent::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }

    /**
     * Send a webhook event
     */
    public function send(WebhookEvent $event): bool
    {
        $endpoint = $event->webhookEndpoint;

        try {
            $payload = [
                'event' => $event->event_type,
                'data' => $event->payload,
                'timestamp' => now()->toIso8601String(),
                'id' => $event->id,
            ];

            $headers = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Liberu-Billing-Webhook/1.0',
            ];

            // Add signature if secret is configured
            if ($endpoint->secret) {
                $signature = hash_hmac('sha256', json_encode($payload), $endpoint->secret);
                $headers['X-Webhook-Signature'] = $signature;
            }

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($endpoint->url, $payload);

            if ($response->successful()) {
                $event->markAsSent();
                $endpoint->update(['last_triggered_at' => now()]);
                return true;
            }

            throw new \Exception('HTTP ' . $response->status() . ': ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Webhook delivery failed', [
                'webhook_event_id' => $event->id,
                'endpoint_url' => $endpoint->url,
                'error' => $e->getMessage(),
            ]);

            if ($event->shouldRetry($endpoint->max_retries)) {
                $event->markAsFailed($e->getMessage(), $endpoint->retry_interval);
            } else {
                $event->update([
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                    'attempts' => $event->attempts + 1,
                ]);
            }

            return false;
        }
    }

    /**
     * Process pending webhooks
     */
    public function processPending(): int
    {
        $processed = 0;
        $events = WebhookEvent::where('status', 'pending')
            ->orWhere(function ($query) {
                $query->where('status', 'failed')
                    ->whereNotNull('next_retry_at')
                    ->where('next_retry_at', '<=', now());
            })
            ->with('webhookEndpoint')
            ->limit(100)
            ->get();

        foreach ($events as $event) {
            $this->send($event);
            $processed++;
        }

        return $processed;
    }

    /**
     * Get all available event types
     */
    public static function getAvailableEvents(): array
    {
        return [
            self::EVENT_INVOICE_CREATED => 'Invoice Created',
            self::EVENT_INVOICE_UPDATED => 'Invoice Updated',
            self::EVENT_INVOICE_PAID => 'Invoice Paid',
            self::EVENT_INVOICE_OVERDUE => 'Invoice Overdue',
            self::EVENT_PAYMENT_RECEIVED => 'Payment Received',
            self::EVENT_PAYMENT_FAILED => 'Payment Failed',
            self::EVENT_PAYMENT_REFUNDED => 'Payment Refunded',
            self::EVENT_SUBSCRIPTION_CREATED => 'Subscription Created',
            self::EVENT_SUBSCRIPTION_UPDATED => 'Subscription Updated',
            self::EVENT_SUBSCRIPTION_CANCELLED => 'Subscription Cancelled',
            self::EVENT_SUBSCRIPTION_RENEWED => 'Subscription Renewed',
            self::EVENT_CLIENT_CREATED => 'Client Created',
            self::EVENT_CLIENT_UPDATED => 'Client Updated',
            self::EVENT_SERVICE_PROVISIONED => 'Service Provisioned',
            self::EVENT_SERVICE_SUSPENDED => 'Service Suspended',
            self::EVENT_SERVICE_TERMINATED => 'Service Terminated',
            self::EVENT_TICKET_CREATED => 'Ticket Created',
            self::EVENT_TICKET_UPDATED => 'Ticket Updated',
            self::EVENT_TICKET_CLOSED => 'Ticket Closed',
        ];
    }

    /**
     * Verify webhook signature
     */
    public static function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}
