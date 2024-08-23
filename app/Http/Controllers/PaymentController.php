<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Currency;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function processPayment(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'currency' => 'required|string|in:USD,GBP,EUR',
        ]);

        // Create a new payment
        $payment = Payment::create($validatedData);

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