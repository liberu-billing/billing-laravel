<?php

namespace App\Console\Commands;

use App\Services\DomainPricingService;

class SyncEnomDomains extends BaseCommand
{
    protected $signature = 'enom:sync-domains';
    protected $description = 'Synchronize domain pricing and TLDs from Enom';

    protected $domainPricingService;

    public function __construct(DomainPricingService $domainPricingService)
    {
        parent::__construct();
        $this->domainPricingService = $domainPricingService;
    }

    public function handle()
    {
        return $this->executeWithLock('sync_enom_domains', function() {
            $this->info('Starting Enom domain synchronization...');

            try {
                $this->domainPricingService->syncTldsFromEnom();
                $this->info('Enom domain synchronization completed successfully.');
                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error('An error occurred during Enom domain synchronization: ' . $e->getMessage());
                return self::FAILURE;
            }
        });
    }
}