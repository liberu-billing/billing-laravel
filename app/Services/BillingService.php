<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\Invoice_Item;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\RecurringBillingConfiguration;
use App\Models\UsageRecord;
use App\Services\PaymentGatewayService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OverdueInvoiceReminder;

class BillingService
{
    protected $serviceProvisioningService;
    protected $paymentPlanService;
    protected $currencyService;
    protected $paymentGatewayService;
    protected $pricingService;


    public function __construct(
        ServiceProvisioningService $serviceProvisioningService,
        CurrencyService $currencyService,
        PaymentPlanService $paymentPlanService = null,
        PaymentGatewayService $paymentGatewayService = null
        PricingService $pricingService = null
    ) {
        $this->serviceProvisioningService = $serviceProvisioningService;
        $this->currencyService = $currencyService;
        $this->paymentPlanService = $paymentPlanService ?? new PaymentPlanService($this);
        $this->paymentGatewayService = $paymentGatewayService ?? new PaymentGatewayService();
    }

    public function createSubscription(Customer $customer, SubscriptionPlan $plan, string $billingCycle)
    {
        $subscription = new Subscription([
            'customer_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => $this->calculateEndDate($billingCycle),
            'renewal_period' => $billingCycle,
            'status' => 'pending',
            'price' => $plan->price,
            'currency' => $plan->currency,
            'auto_renew' => true
        ]);
        
        $subscription->save();
        
        // Generate initial invoice
        $invoice = $this->generateInvoice($subscription);
        
        return $subscription;
    }

    public function upgradeSubscription(Subscription $subscription, SubscriptionPlan $newPlan)
    {
        // Calculate prorated amount
        $proratedAmount = $this->calculateProratedAmount(
            $subscription->price,
            $newPlan->price,
            $subscription->end_date
        );
        
        // Generate upgrade invoice
        $invoice = $this->generateInvoice($subscription, $proratedAmount);
        
        $subscription->update([
            'subscription_plan_id' => $newPlan->id,
            'price' => $newPlan->price
        ]);
        
        return $invoice;
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
            'cancelled_at' => now()
        ]);
        
        // Handle any refunds if necessary
        if ($subscription->end_date->isFuture()) {
            $refundAmount = $this->calculateRefundAmount($subscription);
            if ($refundAmount > 0) {
                $this->processRefund($subscription->lastPayment, $refundAmount);
            }
        }
        
        return true;
    }

    private function calculateProratedAmount($oldPrice, $newPrice, $endDate)
    {
        $daysRemaining = now()->diffInDays($endDate);
        $totalDays = 30; // Assuming monthly billing
        
        $oldAmount = ($oldPrice / $totalDays) * $daysRemaining;
        $newAmount = ($newPrice / $totalDays) * $daysRemaining;
        
        return $newAmount - $oldAmount;
    }

    private function calculateEndDate($billingCycle)
    {
        return match($billingCycle) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi-annually' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    private function calculateRefundAmount(Subscription $subscription)
    {
        $daysRemaining = now()->diffInDays($subscription->end_date);
        $totalDays = $subscription->start_date->diffInDays($subscription->end_date);
        
        return ($subscription->price / $totalDays) * $daysRemaining;
=======
        $this->pricingService = $pricingService ?? new PricingService();
    }

    public function recordUsage(Subscription $subscription, string $metric, float $quantity)
    {
        return $subscription->productService->recordUsage(
            $subscription->id,
            $metric,
            $quantity
        );
    }

    public function calculateUsageCharges(Subscription $subscription, $startDate, $endDate)
    {
        return $this->pricingService->calculatePrice(
            $subscription->productService,
            [
                'subscription_id' => $subscription->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    protected function generateInvoice(Subscription $subscription)
    {
        $amount = $this->calculateUsageCharges(
            $subscription,
            $subscription->last_billed_at ?? $subscription->start_date,
            now()
        );

        // Mark usage records as processed
        UsageRecord::where('subscription_id', $subscription->id)
            ->where('processed', false)
            ->update(['processed' => true]);

        // Create invoice with calculated amount
        $invoice = Invoice::create([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'total_amount' => $amount,
            'currency' => $subscription->currency ?? 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        return $invoice;
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

        // Calculate and add tax
        $taxAmount = $invoice->calculateTax();
        $invoice->update([
            'tax_amount' => $taxAmount,
            'total_amount' => $invoice->final_total
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
        // Process subscription-based billing
        $this->processSubscriptionBilling();
        
        // Process recurring invoices
        $this->processRecurringInvoices();
    }

    protected function processSubscriptionBilling()
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

    protected function processRecurringInvoices()
    {
        $configurations = RecurringBillingConfiguration::where('is_active', true)
            ->where('next_billing_date', '<=', now())
            ->get();

        foreach ($configurations as $config) {
            $originalInvoice = $config->invoice;
            
            // Create new invoice based on original
            $newInvoice = $this->generateRecurringInvoice($originalInvoice);
            
            // Update next billing date
            $config->update([
                'next_billing_date' => $config->calculateNextBillingDate()
            ]);
            
            // Process automatic payment
            $this->processAutomaticPayment($newInvoice);
        }
    }

    protected function generateRecurringInvoice(Invoice $originalInvoice)
    {
        $newInvoice = $originalInvoice->replicate();
        $newInvoice->invoice_number = $this->generateInvoiceNumber();
        $newInvoice->issue_date = now();
        $newInvoice->due_date = now()->addDays(30);
        $newInvoice->status = 'pending';
        $newInvoice->is_recurring = true;
        $newInvoice->save();

        // Copy invoice items
        foreach ($originalInvoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return $newInvoice;
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

    public function sendUpcomingInvoiceReminders()
    {
        $reminderCount = 0;
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $settings = ReminderSetting::where('team_id', $team->id)
                ->where('is_active', true)
                ->first();
                
            if (!$settings) {
                continue;
            }
            
            $upcomingInvoices = Invoice::where('team_id', $team->id)
                ->where('status', 'pending')
                ->where('due_date', '>', Carbon::now())
                ->where('due_date', '<=', Carbon::now()->addDays($settings->days_before_reminder))
                ->whereNull('upcoming_reminder_sent')
                ->get();

            foreach ($upcomingInvoices as $invoice) {
                $this->sendUpcomingInvoiceEmail($invoice);
                $invoice->update(['upcoming_reminder_sent' => true]);
                $reminderCount++;
            }
        }
        
        return $reminderCount;
    }

    private function sendUpcomingInvoiceEmail(Invoice $invoice)
    {
        $customer = $invoice->customer;
        $template = EmailTemplate::where('type', 'upcoming_invoice')
            ->where(function($query) use ($invoice) {
                $query->where('team_id', $invoice->team_id)
                      ->orWhere('is_default', true);
            })
            ->first();

        $data = [
            'customer_name' => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
        ];

        Mail::to($customer->email)
            ->queue(new UpcomingInvoiceReminder($data, $template));
    }

    public function sendOverdueReminders()
    {
        $reminderCount = 0;
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
                    $query->whereNull('reminder_count')
                        ->orWhere('reminder_count', '<', $settings->max_reminders);
                })
                ->where(function ($query) use ($settings) {
                    $query->whereNull('last_reminder_date')
                        ->orWhere('last_reminder_date', '<=', 
                            Carbon::now()->subDays($settings->reminder_frequency));
                })
                ->get();

            foreach ($overdueInvoices as $invoice) {
                // Apply late fee
                $invoice->applyLateFee();
                
                // Send reminder email
                $this->sendOverdueReminderEmail($invoice);
                
                $invoice->update([
                    'reminder_count' => ($invoice->reminder_count ?? 0) + 1,
                    'last_reminder_date' => Carbon::now()
                ]);
                
                // Suspend service if applicable
                $this->serviceProvisioningService->manageService($invoice->subscription, 'suspend');
                
                $reminderCount++;
            }
        }
        
        return $reminderCount;
    }

    public function processLateFees()
    {
        $pendingInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($pendingInvoices as $invoice) {
            try {
                $fee = $invoice->applyLateFee();
                if ($fee > 0) {
                    // Attempt automatic payment for the late fee
                    $this->processAutomaticPayment($invoice);
                    
                    // Send late fee notification
                    $this->sendLateFeeNotification($invoice, $fee);
                }
            } catch (\Exception $e) {
                Log::error('Failed to process late fee', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function sendOverdueReminderEmail(Invoice $invoice)
    {
        $customer = $invoice->customer;
        $data = [
            'customer_name' => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'amount' => $invoice->total_with_late_fee,
            'original_amount' => $invoice->total_amount,
            'late_fee_amount' => $invoice->late_fee_amount,
            'currency' => $invoice->currency,
        ];

        Mail::to($customer->email)->send(new OverdueInvoiceReminder($data));
    }

    private function sendLateFeeNotification(Invoice $invoice, $feeAmount)
    {
        $customer = $invoice->customer;
        Mail::to($customer->email)->send(new LateFeeNotification([
            'customer_name' => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'fee_amount' => $feeAmount,
            'total_amount' => $invoice->total_with_late_fee,
            'currency' => $invoice->currency,
        ]));
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