<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;

class PaymentReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(PaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function index()
    {
        $payments = Payment::with(['invoice', 'customer'])
            ->whereIn('reconciliation_status', ['unmatched', 'discrepancy', 'failed'])
            ->latest()
            ->paginate(10);

        return view('payment-reconciliation.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice', 'customer', 'paymentGateway']);
        $suggestedInvoices = Invoice::where('customer_id', $payment->customer_id)
            ->where('status', 'pending')
            ->get();

        return view('payment-reconciliation.show', compact('payment', 'suggestedInvoices'));
    }

    public function reconcile(Request $request, Payment $payment)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id'
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        
        $result = $this->reconciliationService->handleManualReconciliation($payment, $invoice);

        return redirect()->route('payment-reconciliation.index')
            ->with('status', $result ? 'Payment reconciled successfully' : 'Failed to reconcile payment');
    }
}