<?php

namespace App\Console\Commands;

use Exception;
use App\Services\BillingService;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send SMS reminders for upcoming invoice due dates';

    public function handle(BillingService $billingService)
    {
        $this->info('Sending invoice reminders...');
        
        try {
            $billingService->sendUpcomingDueReminders();
            $this->info('Upcoming due date reminders sent successfully.');
            
            $billingService->sendOverdueReminders();
            $this->info('Overdue reminders sent successfully.');
            
        } catch (Exception $e) {
            $this->error('Error sending reminders: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}