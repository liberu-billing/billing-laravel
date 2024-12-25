

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingService;

class ProcessInvoiceReminders extends Command
{
    protected $signature = 'invoices:process-reminders';
    protected $description = 'Process invoice reminders for upcoming and overdue invoices';

    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    public function handle()
    {
        $this->info('Processing invoice reminders...');
        
        // Process upcoming invoice reminders
        $upcomingCount = $this->billingService->sendUpcomingInvoiceReminders();
        $this->info("Sent {$upcomingCount} upcoming invoice reminders");
        
        // Process overdue invoice reminders
        $overdueCount = $this->billingService->sendOverdueReminders();
        $this->info("Sent {$overdueCount} overdue invoice reminders");
        
        return Command::SUCCESS;
    }
}