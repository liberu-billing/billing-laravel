

<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Log;

class PaymentReconciliationService
{
    public function reconcilePayment(Payment $payment)
    {
        try {
            // Try to match payment with invoice
            $invoice = $this->findMatchingInvoice($payment);
            
            if ($invoice) {
                return $this->processReconciliation($payment, $invoice);
            }
            
            // Mark as unreconciled if no match found
            $payment->update([
                'reconciliation_status' => 'unmatched',
                'reconciliation_notes' => 'No matching invoice found'
            ]);
            
            $this->logReconciliationHistory($payment, null, 'unmatched');
            
            return false;
        } catch (\Exception $e) {
            Log::error('Payment reconciliation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            $payment->update([
                'reconciliation_status' => 'failed',
                'reconciliation_notes' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    protected function findMatchingInvoice(Payment $payment)
    {
        // Try to match by invoice_id if available
        if ($payment->invoice_id) {
            return Invoice::find($payment->invoice_id);
        }
        
        // Try to match by amount and customer
        return Invoice::where('customer_id', $payment->customer_id)
            ->where('total_amount', $payment->amount)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();
    }

    protected function processReconciliation(Payment $payment, Invoice $invoice)
    {
        // Check for discrepancies
        $discrepancy = $this->checkForDiscrepancies($payment, $invoice);
        
        if ($discrepancy) {
            $payment->update([
                'reconciliation_status' => 'discrepancy',
                'reconciliation_notes' => $discrepancy
            ]);
            
            $this->logReconciliationHistory($payment, $invoice, 'discrepancy');
            return false;
        }

        // Process successful reconciliation
        $payment->update([
            'invoice_id' => $invoice->id,
            'reconciliation_status' => 'reconciled',
            'reconciliation_notes' => null
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $payment->amount,
            'paid_date' => now()
        ]);

        $this->logReconciliationHistory($payment, $invoice, 'reconciled');
        return true;
    }

    protected function checkForDiscrepancies(Payment $payment, Invoice $invoice)
    {
        if ($payment->amount != $invoice->total_amount) {
            return "Payment amount ({$payment->amount}) does not match invoice amount ({$invoice->total_amount})";
        }

        if ($payment->currency != $invoice->currency) {
            return "Payment currency ({$payment->currency}) does not match invoice currency ({$invoice->currency})";
        }

        return null;
    }

    protected function logReconciliationHistory(Payment $payment, ?Invoice $invoice, string $status)
    {
        PaymentHistory::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice?->id,
            'customer_id' => $payment->customer_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id,
            'status' => $status,
            'notes' => $payment->reconciliation_notes
        ]);
    }

    public function handleManualReconciliation(Payment $payment, Invoice $invoice)
    {
        return $this->processReconciliation($payment, $invoice);
    }
}