<?php

namespace App\Console\Commands;

use App\Services\BillingService;

class SendInvoiceReminders extends BaseCommand
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send SMS reminders for upcoming invoice due dates';

    public function handle(BillingService $billingService)
    {
        return $this->executeWithLock('send_invoice_reminders', function() use ($billingService) {
            $this->info('Sending invoice reminders...');
            
            try {
                $billingService->sendUpcomingDueReminders();
                $this->info('Upcoming due date reminders sent successfully.');
                
                $billingService->sendOverdueReminders();
                $this->info('Overdue reminders sent successfully.');
                
                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error('Error sending reminders: ' . $e->getMessage());
                return self::FAILURE;
            }
        });
    }
}