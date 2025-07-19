<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Subscription;
use App\Services\ServiceProvisioningService;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    protected $serviceProvisioningService;

    public function __construct(ServiceProvisioningService $serviceProvisioningService)
    {
        $this->serviceProvisioningService = $serviceProvisioningService;
    }

    public function provisionService(Request $request)
    {
        $validatedData = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::findOrFail($validatedData['subscription_id']);

        try {
            $result = $this->serviceProvisioningService->provisionService($subscription);
            return response()->json(['message' => 'Service provisioned successfully', 'result' => $result]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Service provisioning failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function manageService(Request $request)
    {
        $validatedData = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'action' => 'required|in:suspend,unsuspend,terminate',
        ]);

        $subscription = Subscription::findOrFail($validatedData['subscription_id']);

        try {
            $result = $this->serviceProvisioningService->manageService($subscription, $validatedData['action']);
            return response()->json(['message' => 'Service managed successfully', 'result' => $result]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Service management failed', 'error' => $e->getMessage()], 400);
        }
    }
}