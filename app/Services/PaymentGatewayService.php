<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Payment;

class PaymentGatewayService
{
    public function processPayment(Payment $payment)
    {
        $gateway = $payment->paymentGateway;

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
    }

    private function processStripePayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement Stripe payment processing logic here
    }

    private function processAuthorizeNetPayment(Payment $payment, PaymentGateway $gateway)
    {
        // Implement Authorize.net payment processing logic here
    }
}