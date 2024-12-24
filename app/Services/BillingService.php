<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\Invoice_Item;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OverdueInvoiceReminder;

class BillingService
{
    protected $serviceProvisioningService;
    protected $paymentPlanService;
    protected $currencyService;

    public function __construct(
        ServiceProvisioningService $serviceProvisioningService,
        CurrencyService $currencyService,
       PaymentPlanService $paymentPlanService = null
    ) {
        $this->serviceProvisioningService = $serviceProvisioningService;
        $this->currencyService = $currencyService;
       $this->paymentPlanService = $paymentPlanService ?? new PaymentPlanService($this);
    }

    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        return $this->currencyService->convert($amount, $fromCurrency, $toCurrency);
    }

    public function applyDiscount(Invoice $invoice, string $discountCode)
    {
        $discount = Discount::where('code', $discountCode)
            ->where('is_active', true)
            ->first();

        if (!$discount || !$discount->isValid()) {
            return ['success' => false, 'message' => 'Invalid or expired discount code'];
        }

        $discountAmount = $this->calculateDiscountAmount($invoice, $discount);
        
        $invoice->update([
            'discount_id' => $discount->id,
            'discount_amount' => $discountAmount,
            'total_amount' => $invoice->subtotal - $discountAmount
        ]);

        $discount->increment('used_count');

        return ['success' => true, 'discount_amount' => $discountAmount];
    }

    private function calculateDiscountAmount(Invoice $invoice, Discount $discount)
    {
        if ($discount->type === 'percentage') {
            return $invoice->subtotal * ($discount->value / 100);
        }

        if ($discount->type === 'fixed') {
            if ($discount->currency !== $invoice->currency) {
                return $this->convertCurrency(
                    $discount->value,
                    $discount->currency,
                    $invoice->currency
                );
            }
            return $discount->value;
        }

        return 0;
    }

    public function generateInvoice(Subscription $subscription)
    {
        $customer = $subscription->customer;
        $amount = $subscription->productService->price;
        $currency = $subscription->currency ?? 'USD';

        // Get default template or first available
        $template = InvoiceTemplate::where('team_id', $customer->team_id)
            ->where('is_default', true)
            ->first() ?? InvoiceTemplate::where('team_id', $customer->team_id)->first();

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'total_amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'invoice_template_id' => $template?->id,
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

        // Send invoice email
        $invoice->sendInvoiceEmail();

        return $invoice;
    }

    public function setupPaymentPlan(Invoice $invoice, $totalInstallments, $frequency = 'monthly')
    {
        if ($invoice->paymentPlan) {
            throw new \Exception('Invoice already has a payment plan');
        }

        return $invoice->createPaymentPlan($totalInstallments, $frequency);
    }

    public function processPaymentPlans()
    {
        $this->paymentPlanService->processPaymentPlans();
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
            
            // Process automatic payment
            $paymentResult = $this->processAutomaticPayment($invoice);
            
            if ($paymentResult['success']) {
                $invoice->update(['status' => 'paid']);
                $subscription->renew();
                $this->serviceProvisioningService->manageService($subscription, 'unsuspend');
            } else {
                $this->serviceProvisioningService->manageService($subscription, 'suspend');
                // TODO: Implement logic to notify customer of failed payment
            }
        }
    }

    public function processAutomaticPayment(Invoice $invoice)
    {
        $paymentGatewayService = new PaymentGatewayService();
        $customer = $invoice->customer;
        
        // Assuming the customer has a default payment method stored
        $paymentMethod = $customer->defaultPaymentMethod;
        
        if (!$paymentMethod) {
            return ['success' => false, 'message' => 'No default payment method found'];
        }
        
        $payment = new Payment([
            'invoice_id' => $invoice->id,
            'payment_gateway_id' => $paymentMethod->payment_gateway_id,
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'payment_method' => $paymentMethod->type,
        ]);
        
        try {
            $result = $paymentGatewayService->processPayment($payment);
            if ($result['success']) {
                $payment->transaction_id = $result['transaction_id'];
                $payment->save();
                return ['success' => true, 'payment' => $payment];
            } else {
                return ['success' => false, 'message' => $result['message']];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()];
        }
    }

    public function sendOverdueReminders()
    {
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $settings = ReminderSetting::where('team_id', $team->id)
                ->where('is_active', true)
                ->first();
                
            if (!$settings) {
                continue;
            }
            
            $overdueInvoices = Invoice::where('team_id', $team->id)
                ->where('due_date', '<', Carbon::now())
                ->where('status', 'pending')
                ->where(function ($query) use ($settings) {
                    // Only get invoices that haven't exceeded max reminders
                    $query->whereNull('reminder_count')
                        ->orWhere('reminder_count', '<', $settings->max_reminders);
                })
                ->where(function ($query) use ($settings) {
                    // Only get invoices that are due for a reminder
                    $query->whereNull('last_reminder_date')
                        ->orWhere('last_reminder_date', '<=', 
                            Carbon::now()->subDays($settings->reminder_frequency));
                })
                ->get();

            foreach ($overdueInvoices as $invoice) {
                $this->sendOverdueReminderEmail($invoice);
                
                $invoice->update([
                    'reminder_count' => ($invoice->reminder_count ?? 0) + 1,
                    'last_reminder_date' => Carbon::now()
                ]);
                
                $this->serviceProvisioningService->manageService($invoice->subscription, 'suspend');
            }
        $overdueInvoices = Invoice::where('due_date', '<', Carbon::now())
            ->where('status', 'pending')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Apply late fee
            $invoice->applyLateFee();
            
            // Send overdue reminder email
            $this->sendOverdueReminderEmail($invoice);
            
            // Suspend service if applicable
            $this->serviceProvisioningService->manageService($invoice->subscription, 'suspend');
        }
    }

    public function processLateFees()
    {
        $pendingInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($pendingInvoices as $invoice) {
            $invoice->applyLateFee();
        }
    }

    private function sendOverdueReminderEmail(Invoice $invoice)
    {
        $customer = $invoice->customer;
        $data = [
            'customer_name' => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
        ];

        Mail::to($customer->email)->send(new OverdueInvoiceReminder($data));
    }

    private function generateInvoiceNumber()
    {
        return 'INV-' . strtoupper(uniqid());
    }

    public function handlePartialPayment(Invoice $invoice, float $amount, int $paymentGatewayId)
    {
        $partialPaymentService = new PartialPaymentService(new PaymentGatewayService());
        return $partialPaymentService->processPartialPayment($invoice, $amount, $paymentGatewayId);
    }

    public function handleRefund(Payment $payment, float $amount)
    {
        $refundService = new RefundService(new PaymentGatewayService());
        return $refundService->processRefund($payment, $amount);
    }
}