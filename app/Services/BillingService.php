<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Customer;
use App\Models\HostingAccount;
use Carbon\Carbon;

class BillingService
{
    protected $hostingService;

    public function __construct(HostingService $hostingService)
    {
        $this->hostingService = $hostingService;
    }

    public function generateInvoice(Subscription $subscription)
    {
        $customer = $subscription->customer;
        $amount = $subscription->productService->price;
        $currency = $subscription->currency ?? 'USD'; // Default to USD if not specified
    
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'total_amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
        ]);
    
        // Create invoice item
        Invoice_Item::create([
            'invoice_id' => $invoice->id,
            'product_service_id' => $subscription->productService->id,
            'quantity' => 1,
            'unit_price' => $amount,
            'total_price' => $amount,
            'currency' => $currency,
        ]);
    
        // TODO: Send invoice email
        return $invoice;
    }
    
    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }
    
        $fromRate = Currency::where('code', $fromCurrency)->first()->exchange_rate;
        $toRate = Currency::where('code', $toCurrency)->first()->exchange_rate;
    
        return ($amount / $fromRate) * $toRate;
    }

    public function processRecurringBilling()
    {
        $dueSubscriptions = Subscription::where('end_date', '<=', Carbon::now())
            ->where('status', 'active')
            ->get();

        foreach ($dueSubscriptions as $subscription) {
            $invoice = $this->generateInvoice($subscription);
            $subscription->update([
                'end_date' => Carbon::parse($subscription->end_date)->add($subscription->renewal_period),
            ]);

            if ($invoice->status === 'paid') {
                $this->ensureHostingAccountActive($subscription);
            } else {
                $this->suspendHostingAccount($subscription);
            }
        }
    }

    public function sendOverdueReminders()
    {
        $overdueInvoices = Invoice::where('due_date', '<', Carbon::now())
            ->where('status', 'pending')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // TODO: Send overdue reminder email
            $this->suspendHostingAccount($invoice->subscription);
        }
    }

    private function generateInvoiceNumber()
    {
        return 'INV-' . strtoupper(uniqid());
    }

    private function ensureHostingAccountActive(Subscription $subscription)
    {
        $hostingAccount = HostingAccount::where('subscription_id', $subscription->id)->first();
        if ($hostingAccount && !$hostingAccount->isActive()) {
            $this->hostingService->unsuspendAccount($hostingAccount);
        }
    }

    private function suspendHostingAccount(Subscription $subscription)
    {
        $hostingAccount = HostingAccount::where('subscription_id', $subscription->id)->first();
        if ($hostingAccount && $hostingAccount->isActive()) {
            $this->hostingService->suspendAccount($hostingAccount);
        }
    }
}