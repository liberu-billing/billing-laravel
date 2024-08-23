<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\CardException;

class PaymentGatewayService
{
    public function processPayment(Payment $payment, $stripeToken = null)
    {
        $gateway = $payment->paymentGateway;

        switch ($gateway->name) {
            case 'PayPal':
                return $this->processPayPalPayment($payment, $gateway);
            case 'Stripe':
                return $this->processStripePayment($payment, $gateway, $stripeToken);
            case 'Authorize.net':
                return $this->processAuthorizeNetPayment($payment, $gateway);
            default:
                throw new \Exception('Unsupported payment gateway');
        }
    }

    private function processPayPalPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement PayPal payment processing logic here
    }

    private function processStripePayment(Payment $payment, PaymentGateway $gateway, $stripeToken)
    {
        if (!$stripeToken) {
            throw new \Exception('Stripe token is required for payment processing');
        }

        Stripe::setApiKey($gateway->secret_key);

        try {
            $charge = Charge::create([
                'amount' => $payment->amount * 100, // Amount in cents
                'currency' => $payment->currency,
                'source' => $stripeToken,
                'description' => "Payment for invoice #{$payment->invoice_id}",
            ]);

            $payment->transaction_id = $charge->id;
            $payment->save();

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $charge->id,
            ];
        } catch (CardException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function processAuthorizeNetPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement Authorize.net payment processing logic here
    }
}