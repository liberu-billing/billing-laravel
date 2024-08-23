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

    public function __construct(ServiceProvisioningService $serviceProvisioningService)
    {
        $this->serviceProvisioningService = $serviceProvisioningService;
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

        // Send invoice email
        $invoice->sendInvoiceEmail();

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
        $overdueInvoices = Invoice::where('due_date', '<', Carbon::now())
            ->where('status', 'pending')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Send overdue reminder email
            $this->sendOverdueReminderEmail($invoice);
            $this->serviceProvisioningService->manageService($invoice->subscription, 'suspend');
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
}