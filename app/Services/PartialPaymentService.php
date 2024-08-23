<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\DB;

class PartialPaymentService
{
    protected $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function processPartialPayment(Invoice $invoice, float $amount, int $paymentGatewayId)
    {
        if ($amount <= 0 || $amount > $invoice->remaining_amount) {
            throw new \Exception('Invalid partial payment amount.');
        }

        DB::beginTransaction();

        try {
            $payment = new Payment([
                'invoice_id' => $invoice->id,
                'payment_gateway_id' => $paymentGatewayId,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'payment_date' => now(),
            ]);

            $paymentResult = $this->paymentGatewayService->processPayment($payment);

            if ($paymentResult['success']) {
                $payment->transaction_id = $paymentResult['transaction_id'];
                $payment->save();

                $this->updateInvoiceStatus($invoice);

                DB::commit();
                return ['success' => true, 'message' => 'Partial payment processed successfully.', 'payment' => $payment];
            } else {
                throw new \Exception($paymentResult['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Partial payment failed: ' . $e->getMessage()];
        }
    }

    private function updateInvoiceStatus(Invoice $invoice)
    {
        $totalPaid = $invoice->payments->sum('amount');

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->status = 'paid';
        } elseif ($totalPaid > 0) {
            $invoice->status = 'partially_paid';
        }

        $invoice->save();
    }
}