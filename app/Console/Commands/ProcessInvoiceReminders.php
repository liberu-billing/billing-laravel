<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Services\BillingService;

class ProcessInvoiceReminders extends Command
{
    protected $signature = 'invoices:process-reminders';
    protected $description = 'Process invoice reminders for upcoming and overdue invoices';

    protected $billingService;

    public function __construct($serviceProvisioningService = null, $currencyService = null)
    {
        parent::__construct();
        if($serviceProvisioningService == null || $currencyService == null){
            $this->billingService = null;
        }
        else{
            $this->billingService = new BillingService($serviceProvisioningService, $currencyService);
        }
    }

    public function handle()
    {
        if (cache()->get('processing_invoice_reminders')) {
            $this->warn('Invoice reminder processing is already running');
            return Command::FAILURE;
        }

        cache()->put('processing_invoice_reminders', true, 60); // Lock for 60 minutes

        try {
            $this->info('Processing invoice reminders...');

            $upcomingCount = $this->billingService->sendUpcomingInvoiceReminders();
            $this->info("Sent {$upcomingCount} upcoming invoice reminders");

            $overdueCount = $this->billingService->sendOverdueReminders();
            $this->info("Sent {$overdueCount} overdue invoice reminders");

            cache()->forget('processing_invoice_reminders');
            return Command::SUCCESS;
        } catch (Exception $e) {
            cache()->forget('processing_invoice_reminders');
            $this->error("Error processing reminders: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
