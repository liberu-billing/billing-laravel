

<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    use PreventRecursion;

    public function generateInvoices($billingCycle)
    {
        if (!$this->preventRecursion('generate_invoices_' . $billingCycle)) {
            Log::warning('Invoice generation already in progress for cycle: ' . $billingCycle);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('generate_invoices_' . $billingCycle);
        }
    }

    public function processPayment($invoice, $amount)
    {
        if (!$this->preventRecursion('process_payment_' . $invoice->id)) {
            Log::warning('Payment processing already in progress for invoice: ' . $invoice->id);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('process_payment_' . $invoice->id);
        }
    }
}