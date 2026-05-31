<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Services\DomainPricingService;

#[\Illuminate\Console\Attributes\Description('Synchronize domain pricing and TLDs from Enom')]
#[\Illuminate\Console\Attributes\Signature('enom:sync-domains')]
class SyncEnomDomains extends Command
{
    public function __construct(protected \App\Services\DomainPricingService $domainPricingService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting Enom domain synchronization...');

        try {
            $this->domainPricingService->syncTldsFromEnom();
            $this->info('Enom domain synchronization completed successfully.');
        } catch (Exception $e) {
            $this->error('An error occurred during Enom domain synchronization: ' . $e->getMessage());
        }
    }
}
