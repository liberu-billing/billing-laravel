<?php

namespace App\Console\Commands;

use App\Services\WebhookService;
use Exception;
use Illuminate\Console\Command;

class ProcessWebhooks extends Command
{
    protected $signature = 'webhooks:process';
    protected $description = 'Process pending webhook events';

    public function __construct(
        protected ?WebhookService $webhookService = null
    ) {
        parent::__construct();
        $this->webhookService = $webhookService ?? app(WebhookService::class);
    }

    public function handle()
    {
        if (cache()->get('processing_webhooks')) {
            $this->warn('Webhook processing is already running');
            return Command::FAILURE;
        }

        cache()->put('processing_webhooks', true, 60); // Lock for 60 minutes

        try {
            $this->info('Processing pending webhooks...');

            $processed = $this->webhookService->processPending();
            
            $this->info("Processed {$processed} webhook events");

            cache()->forget('processing_webhooks');
            return Command::SUCCESS;
        } catch (Exception $e) {
            cache()->forget('processing_webhooks');
            $this->error("Error processing webhooks: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
