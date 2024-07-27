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

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'total_amount' => $amount,
            'status' => 'pending',
        ]);

        // TODO: Send invoice email
        return $invoice;
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

    public function addCredit(Customer $customer, $amount, $description = null, $expiryDate = null)
    {
        return Credit::create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'description' => $description,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function useCredit(Invoice $invoice)
    {
        $customer = $invoice->customer;
        $availableCredits = $customer->credits()
            ->where('amount', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->orderBy('expiry_date')
            ->get();

        $remainingAmount = $invoice->total_amount;

        foreach ($availableCredits as $credit) {
            if ($remainingAmount <= 0) {
                break;
            }

            $usedAmount = min($credit->amount, $remainingAmount);
            $credit->amount -= $usedAmount;
            $credit->save();

            $remainingAmount -= $usedAmount;
        }

        if ($remainingAmount < $invoice->total_amount) {
            $invoice->total_amount = $remainingAmount;
            $invoice->save();
        }

        return $invoice;
    }

    public function processRefund(Payment $payment, $amount)
    {
        if (!$payment->isRefundable() || $amount > $payment->amount) {
            throw new \Exception('Invalid refund request');
        }

        $payment->refund_status = 'pending';
        $payment->save();

        // TODO: Implement actual refund logic with payment gateway

        $payment->refund_status = 'completed';
        $payment->amount -= $amount;
        $payment->save();

        $this->addCredit($payment->invoice->customer, $amount, 'Refund for payment #' . $payment->id);

        return $payment;
    }
}