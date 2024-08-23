<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

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
            'stripe_token' => 'required_if:payment_method,stripe|string',
        ]);

        // Create a new payment
        $payment = Payment::create($validatedData);

        // Process the payment using the appropriate gateway
        try {
            $result = $this->paymentGatewayService->processPayment($payment, $request->input('stripe_token'));
            return response()->json(['message' => 'Payment processed successfully', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 400);
        }
    }
}