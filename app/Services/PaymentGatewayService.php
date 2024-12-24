<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Payment;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\CardException;

class PaymentGatewayService
{
    private $maxRetries = 3;
    private $retryDelay = 5; // seconds

    public function processPayment(Payment $payment)
    {
        $gateway = $payment->paymentGateway;
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                $result = $this->attemptPayment($payment, $gateway);
                Log::info('Payment processed successfully', ['payment_id' => $payment->id, 'attempt' => $retries + 1]);
                return $result;
            } catch (\Exception $e) {
                $retries++;
                Log::warning('Payment attempt failed', [
                    'payment_id' => $payment->id,
                    'attempt' => $retries,
                    'error' => $e->getMessage()
                ]);

                if ($retries >= $this->maxRetries) {
                    Log::error('Payment processing failed after max retries', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

                sleep($this->retryDelay);
            }
        }
    }
  
    private function recordPaymentHistory(Payment $payment, $status, $notes = null)
    {
        PaymentHistory::create([
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'customer_id' => $payment->invoice->customer_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id,
            'status' => $status,
            'notes' => $notes
        ]);
    }

    private function attemptPayment(Payment $payment, PaymentGateway $gateway)
    {
        try {
            $result = match ($gateway->name) {
                'PayPal' => $this->processPayPalPayment($payment, $gateway),
                'Stripe' => $this->processStripePayment($payment, $gateway),
                'Authorize.net' => $this->processAuthorizeNetPayment($payment, $gateway),
                default => throw new \Exception('Unsupported payment gateway'),
            };
            
            $this->recordPaymentHistory($payment, 'completed');
            return $result;
        } catch (\Exception $e) {
            $this->recordPaymentHistory($payment, 'failed', $e->getMessage());
            throw $e;
        }
    }

    private function processPayPalPayment(Payment $payment, PaymentGateway $gateway)
    {
        $environment = new SandboxEnvironment($gateway->api_key, $gateway->secret_key);
        $client = new PayPalHttpClient($environment);

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $payment->currency,
                    'value' => number_format($payment->amount, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel')
            ]
        ];

        try {
            $response = $client->execute($request);
            
            $payment->update([
                'transaction_id' => $response->result->id,
                'status' => 'pending'
            ]);

            return [
                'status' => 'success',
                'redirect_url' => $response->result->links[1]->href
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            $payment->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function processStripePayment(Payment $payment, PaymentGateway $gateway)
    {
        $stripeToken = $payment->stripe_token;

        if (!$stripeToken) {
            throw new \Exception('Stripe token is required for payment processing');
        }

        \Stripe\Stripe::setApiKey($gateway->secret_key);

        try {
            $charge = \Stripe\Charge::create([
                'amount' => $payment->amount * 100,
                'currency' => strtolower($payment->currency),
                'source' => $stripeToken,
                'description' => "Payment for Invoice #{$payment->invoice_id}",
                'metadata' => [
                    'invoice_id' => $payment->invoice_id,
                    'customer_id' => $payment->invoice->customer_id
                ]
            ]);

            $payment->update([
                'transaction_id' => $charge->id,
                'status' => 'completed'
            ]);

            return [
                'status' => 'success',
                'charge_id' => $charge->id
            ];
        } catch (\Stripe\Exception\CardException $e) {
            $payment->update(['status' => 'failed']);
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
    }
}