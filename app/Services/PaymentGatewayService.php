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
use Square\SquareClient;
use Square\Exceptions\ApiException;

class PaymentGatewayService
{
    private $maxRetries = 3;
    private $retryDelay = 5; // seconds
    private $supportedMethods = [
        'credit_card',
        'debit_card',
        'paypal',
        'google_pay',
        'apple_pay',
        'square',
        'bank_transfer'
    ];

    public function validatePaymentMethod($method)
    {
        return in_array($method, $this->supportedMethods);
    }

    public function processPayment(Payment $payment)
    {
        $gateway = $payment->paymentGateway;
        $retries = 0;

        if (!$this->validatePaymentMethod($payment->payment_method)) {
            throw new \Exception('Unsupported payment method: ' . $payment->payment_method);
        }

        while ($retries < $this->maxRetries) {
            try {
                $result = $this->attemptPayment($payment, $gateway);
                Log::info('Payment processed successfully', [
                    'payment_id' => $payment->id, 
                    'attempt' => $retries + 1,
                    'method' => $payment->payment_method
                ]);
                return $result;
            } catch (\Exception $e) {
                $retries++;
                Log::warning('Payment attempt failed', [
                    'payment_id' => $payment->id,
                    'attempt' => $retries,
                    'method' => $payment->payment_method,
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
            case 'Square':
                return $this->processSquarePayment($payment, $gateway);
            case 'Google Pay':
                return $this->processGooglePayPayment($payment, $gateway);
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

    private function processSquarePayment(Payment $payment, PaymentGateway $gateway)
    {
        $client = new SquareClient([
            'accessToken' => $gateway->secret_key,
            'environment' => config('services.square.environment'),
        ]);

        try {
            $amount = (int)($payment->amount * 100);
            $currency = strtoupper($payment->currency);

            $response = $client->getPaymentsApi()->createPayment([
                'source_id' => $payment->square_token,
                'amount_money' => [
                    'amount' => $amount,
                    'currency' => $currency
                ],
                'idempotency_key' => uniqid('', true),
                'reference_id' => (string)$payment->id
            ]);

            if ($response->isSuccess()) {
                $payment->update([
                    'transaction_id' => $response->getResult()->getPayment()->getId(),
                    'status' => 'completed'
                ]);
                return $response->getResult()->getPayment();
            }

            throw new \Exception($response->getErrors()[0]->getDetail());
        } catch (ApiException $e) {
            $payment->update(['status' => 'failed']);
            throw new \Exception('Square payment failed: ' . $e->getMessage());
        }
    }

    private function processGooglePayPayment(Payment $payment, PaymentGateway $gateway)
    {
        try {
            $paymentToken = $payment->google_pay_token;
            if (!$paymentToken) {
                throw new \Exception('Google Pay token is required');
            }

            // Process payment through Google Pay API
            $response = $this->processGooglePayToken($paymentToken, $payment, $gateway);

            $payment->update([
                'transaction_id' => $response['transaction_id'],
                'status' => $response['status']
            ]);

            return $response;
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            throw new \Exception('Google Pay payment failed: ' . $e->getMessage());
        }
    }

    private function processGooglePayToken($token, Payment $payment, PaymentGateway $gateway)
    {
        // Implement actual Google Pay API integration here
        // This is a placeholder implementation
        return [
            'success' => true,
            'transaction_id' => 'gp_' . uniqid(),
            'status' => 'completed'
        ];
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