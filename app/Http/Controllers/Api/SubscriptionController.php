<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::query()
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(15);

        return response()->json($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_service_id' => 'required|exists:products_services,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'renewal_period' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'auto_renew' => 'boolean',
        ]);

        $subscription = Subscription::create($validated);

        return response()->json($subscription, 201);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        return response()->json($subscription);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'end_date' => 'nullable|date',
            'renewal_period' => 'string',
            'price' => 'numeric|min:0',
            'currency' => 'string|size:3',
            'auto_renew' => 'boolean',
            'status' => 'string|in:active,cancelled,suspended,expired',
        ]);

        $subscription->update($validated);

        return response()->json($subscription);
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $subscription->delete();

        return response()->json(null, 204);
    }

    public function cancel(Subscription $subscription): JsonResponse
    {
        $subscription->update(['status' => 'cancelled']);

        return response()->json($subscription);
    }

    public function renew(Subscription $subscription): JsonResponse
    {
        $subscription->update(['status' => 'active']);

        return response()->json($subscription);
    }
}
