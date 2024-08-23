<?php

namespace App\Http\Controllers;

use App\Models\Products_Service;
use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Invoice_Item;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create()
    {
        $packages = Products_Service::where('type', 'hosting')->get();
        return view('orders.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:products_services,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'domain' => 'required|string|max:255',
            'payment_method' => 'required|string|in:credit_card,paypal',
            'stripe_token' => 'required_if:payment_method,credit_card|string',
        ]);

        // Create or update customer
        $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            ['name' => $request->name]
        );

        // Get the selected package
        $package = Products_Service::findOrFail($request->package_id);

        // Create subscription
        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'product_service_id' => $package->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'renewal_period' => 'yearly',
            'status' => 'active',
        ]);

        // Create hosting account
        $hostingAccount = HostingAccount::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'domain' => $request->domain,
            'package' => $package->name,
            'status' => 'pending',
        ]);

        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'total_amount' => $package->price,
            'status' => 'pending',
        ]);

        // Create invoice item
        Invoice_Item::create([
            'invoice_id' => $invoice->id,
            'product_service_id' => $package->id,
            'quantity' => 1,
            'unit_price' => $package->price,
            'total_price' => $package->price,
        ]);

        // Process payment
        $paymentController = new PaymentController();
        $paymentResult = $paymentController->processPayment(new Request([
            'invoice_id' => $invoice->id,
            'payment_gateway_id' => $request->payment_method === 'credit_card' ? 2 : 1, // Assuming 2 is Stripe and 1 is PayPal
            'amount' => $package->price,
            'payment_method' => $request->payment_method,
            'stripe_token' => $request->stripe_token,
        ]));

        if ($paymentResult->status() === 200) {
            // Payment successful, update statuses
            $invoice->update(['status' => 'paid']);
            $hostingAccount->update(['status' => 'active']);
            return redirect()->route('orders.confirmation', $invoice->id)->with('success', 'Your order has been placed successfully!');
        } else {
            // Payment failed, rollback changes
            $subscription->delete();
            $hostingAccount->delete();
            $invoice->delete();
            return back()->withErrors(['payment' => 'Payment processing failed. Please try again.']);
        }
    }

    public function confirmation($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        return view('orders.confirmation', compact('invoice'));
    }
}