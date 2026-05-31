<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use App\Models\Customer;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $query = PaymentHistory::with(['payment', 'invoice', 'customer']);
        
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $paymentHistories = $query->latest()->paginate(10);
        
        return view('payment-history.index', compact('paymentHistories'));
    }

    public function customerHistory($customerId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $customer = Customer::findOrFail($customerId);
        $paymentHistories = PaymentHistory::where('customer_id', $customerId)
            ->with(['payment', 'invoice'])
            ->latest()
            ->paginate(10);
            
        return view('payment-history.customer', compact('customer', 'paymentHistories'));
    }
}