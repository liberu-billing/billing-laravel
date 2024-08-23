<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Payment;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
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
  
    private function attemptPayment(Payment $payment, PaymentGateway $gateway)
    {
        switch ($gateway->name) {
            case 'PayPal':
                return $this->processPayPalPayment($payment, $gateway);
            case 'Stripe':
                return $this->processStripePayment($payment, $gateway);
            case 'Authorize.net':
                return $this->processAuthorizeNetPayment($payment, $gateway);
            default:
                throw new \Exception('Unsupported payment gateway');
        }
    }

    private function processPayPalPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement PayPal payment processing logic here
        // Include currency handling
        $currency = Currency::where('code', $payment->currency)->firstOrFail();
        // Use $currency->code for PayPal API calls
    }

    private function processStripePayment(Payment $payment, PaymentGateway $gateway)
    {
        // Retrieve the Stripe token from the payment data
        $stripeToken = $payment->stripe_token;

        if (!$stripeToken) {
            throw new \Exception('Stripe token is required for payment processing');
        }

        // Set up Stripe API key
        \Stripe\Stripe::setApiKey($gateway->secret_key);

        try {
            // Create a charge using the Stripe token
            $charge = \Stripe\Charge::create([
                'amount' => $payment->amount * 100, // Amount in cents
                'currency' => $payment->currency,
                'source' => $stripeToken,
                'description' => 'Payment for Invoice #' . $payment->invoice_id,
            ]);

            // Update payment with Stripe charge ID
            $payment->update([
                'transaction_id' => $charge->id,
                'status' => 'completed',
            ]);

            return $charge;
        } catch (\Stripe\Exception\CardException $e) {
            // Handle failed charge
            $payment->update(['status' => 'failed']);
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
        // Implement Stripe payment processing logic here
        // Include currency handling
        $currency = Currency::where('code', $payment->currency)->firstOrFail();
        // Use $currency->code for Stripe API calls
    }

    private function processAuthorizeNetPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement Authorize.net payment processing logic here
        // Include currency handling
        $currency = Currency::where('code', $payment->currency)->firstOrFail();
        // Use $currency->code for Authorize.net API calls
    }
}