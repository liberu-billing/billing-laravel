<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentHistory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request): Factory|View
    {
        $query = PaymentHistory::with(
            [
                'payment',
                'invoice',
                'customer'
            ]
        );

        if ($request->has('customer_id')) {
            $query->where(
                'customer_id',
                $request->customer_id
            );
        }

        $paymentHistories = $query->latest()->paginate(10);

        return view(
            'payment-history.index',
            compact('paymentHistories')
        );
    }

    public function customerHistory($customerId): Factory|View
    {
        $customer = Customer::findOrFail($customerId);
        $paymentHistories = PaymentHistory::where(
            'customer_id',
            $customerId
        )
            ->with(
                [
                    'payment',
                    'invoice'
                ]
            )
            ->latest()
            ->paginate(10);

        return view(
            'payment-history.customer',
            compact(
                'customer',
                'paymentHistories'
            )
        );
    }
}
