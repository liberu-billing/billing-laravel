<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Currency;
use App\Models\Invoice;
use App\Services\PaymentGatewayService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentGatewayService;
    protected $currencyService;

    public function __construct(
        PaymentGatewayService $paymentGatewayService,
        CurrencyService $currencyService
    ) {
        $this->paymentGatewayService = $paymentGatewayService;
        $this->currencyService = $currencyService;
    }

    public function processPayment(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'currency' => 'required|string|exists:currencies,code',
            'stripe_token' => 'required_if:payment_method,stripe|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Convert amount if payment currency differs from invoice currency
        if ($request->currency !== $invoice->currency) {
            $validatedData['amount'] = $this->currencyService->convert(
                $validatedData['amount'],
                $invoice->currency,
                $request->currency
            );
        }

        // Create a new payment
        $payment = Payment::create($validatedData);

        // If it's a Stripe payment, add the token to the payment data
        if ($request->payment_method === 'stripe') {
            $payment->stripe_token = $request->stripe_token;
            $payment->save();
        }

        // Process the payment using the appropriate gateway
        try {
            $result = $this->paymentGatewayService->processPayment($payment);
            Log::info('Payment processed successfully', ['payment_id' => $payment->id, 'result' => $result]);
            return response()->json(['message' => 'Payment processed successfully', 'result' => $result]);
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 400);
        }
    }
}