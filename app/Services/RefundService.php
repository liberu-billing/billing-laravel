<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class RefundService
{
    use PreventRecursion;

    protected $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function processRefund($payment, $amount)
    {
        if (!$this->preventRecursion('process_refund_' . $payment->id)) {
            Log::warning('Refund already being processed for payment ' . $payment->id);
            return false;
        }

        try {
            $refundResult = $this->paymentGatewayService->refundPayment($payment, $amount);

            if ($refundResult['success']) {
                $payment->refund_status = $amount == $payment->amount ? 'full' : 'partial';
                $payment->refunded_amount = ($payment->refunded_amount ?? 0) + $amount;
                $payment->save();

                // Update invoice status if necessary
                $this->updateInvoiceStatus($payment->invoice);

                DB::commit();
                return ['success' => true, 'message' => 'Refund processed successfully.'];
            } else {
                throw new \Exception($refundResult['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Refund failed: ' . $e->getMessage()];
        }
    }

    private function updateInvoiceStatus($invoice)
    {
        $totalPaid = $invoice->payments->sum('amount');
        $totalRefunded = $invoice->payments->sum('refunded_amount');
        $netPaid = $totalPaid - $totalRefunded;

        if ($netPaid <= 0) {
            $invoice->status = 'refunded';
        } elseif ($netPaid < $invoice->total_amount) {
            $invoice->status = 'partially_paid';
        }

        $invoice->save();
    }
}