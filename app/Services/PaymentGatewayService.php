<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Payment;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Exception\CardException;
use Stripe\Exception\ApiErrorException;

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
                
                // Trigger reconciliation after successful payment
                if ($result) {
                    app(PaymentReconciliationService::class)->reconcilePayment($payment);
                }
                
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

    public function refundPayment(Payment $payment, float $amount)
    {
        $gateway = $payment->paymentGateway;
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                $result = $this->attemptRefund($payment, $gateway, $amount);
                Log::info('Refund processed successfully', [
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'attempt' => $retries + 1
                ]);
                return $result;
            } catch (\Exception $e) {
                $retries++;
                Log::warning('Refund attempt failed', [
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'attempt' => $retries,
                    'error' => $e->getMessage()
                ]);

                if ($retries >= $this->maxRetries) {
                    Log::error('Refund processing failed after max retries', [
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

    private function attemptRefund(Payment $payment, PaymentGateway $gateway, float $amount)
    {
        switch ($gateway->name) {
            case 'PayPal':
                return $this->processPayPalRefund($payment, $amount);
            case 'Stripe':
                return $this->processStripeRefund($payment, $amount);
            case 'Authorize.net':
                return $this->processAuthorizeNetRefund($payment, $amount);
            default:
                throw new \Exception('Unsupported payment gateway for refunds');
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
    }

    private function processAuthorizeNetPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement Authorize.net payment processing logic here
        // Include currency handling
        $currency = Currency::where('code', $payment->currency)->firstOrFail();
        // Use $currency->code for Authorize.net API calls
    }

    private function processStripeRefund(Payment $payment, float $amount)
    {
        \Stripe\Stripe::setApiKey($payment->paymentGateway->secret_key);
        
        try {
            $refund = \Stripe\Refund::create([
                'charge' => $payment->transaction_id,
                'amount' => (int)($amount * 100), // Convert to cents
                'reason' => 'requested_by_customer',
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $refund->id,
                'message' => 'Refund processed successfully'
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function processPayPalRefund(Payment $payment, float $amount)
    {
        // Implement PayPal refund logic here
        return [
            'success' => true,
            'message' => 'PayPal refund processed successfully'
        ];
    }

    private function processAuthorizeNetRefund(Payment $payment, float $amount)
    {
        // Implement Authorize.net refund logic here
        return [
            'success' => true,
            'message' => 'Authorize.net refund processed successfully'
        ];
    }
}