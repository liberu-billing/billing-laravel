

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Products_Service;
use App\Services\BillingService;
use App\Services\HostingService;
use App\Services\DomainService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceManagementController extends Controller
{
    protected $billingService;
    protected $hostingService;
    protected $domainService;

    public function __construct(
        BillingService $billingService,
        HostingService $hostingService,
        DomainService $domainService
    ) {
        $this->billingService = $billingService;
        $this->hostingService = $hostingService;
        $this->domainService = $domainService;
    }

    public function index()
    {
        $subscriptions = auth()->user()->customer->subscriptions;
        return view('client.services.index', compact('subscriptions'));
    }

    public function show(Subscription $subscription)
    {
        $availableUpgrades = Products_Service::where('type', $subscription->productService->type)
            ->where('price', '>', $subscription->productService->price)
            ->get();
            
        $availableDowngrades = Products_Service::where('type', $subscription->productService->type)
            ->where('price', '<', $subscription->productService->price)
            ->get();

        return view('client.services.show', compact('subscription', 'availableUpgrades', 'availableDowngrades'));
    }

    public function upgrade(Request $request, Subscription $subscription)
    {
        $request->validate([
            'new_service_id' => 'required|exists:products_services,id'
        ]);

        $newService = Products_Service::findOrFail($request->new_service_id);
        
        // Calculate prorated amount
        $proratedAmount = $this->calculateProration($subscription, $newService);
        
        // Generate invoice for upgrade
        $invoice = $this->billingService->generateInvoice($subscription);
        $invoice->total_amount = $proratedAmount;
        $invoice->save();

        // Update service
        if ($subscription->productService->type === 'hosting') {
            $this->hostingService->upgradeAccount($subscription->hostingAccount, $newService);
        }

        $subscription->product_service_id = $newService->id;
        $subscription->save();

        return redirect()->route('client.services.show', $subscription)
            ->with('success', 'Service upgraded successfully');
    }

    public function downgrade(Request $request, Subscription $subscription)
    {
        $request->validate([
            'new_service_id' => 'required|exists:products_services,id'
        ]);

        $newService = Products_Service::findOrFail($request->new_service_id);
        
        // Schedule downgrade for end of billing period
        $subscription->scheduled_change = [
            'type' => 'downgrade',
            'product_service_id' => $newService->id,
            'effective_date' => $subscription->end_date
        ];
        $subscription->save();

        return redirect()->route('client.services.show', $subscription)
            ->with('success', 'Service scheduled for downgrade at end of billing period');
    }

    public function cancel(Subscription $subscription)
    {
        // Schedule cancellation for end of billing period
        $subscription->scheduled_change = [
            'type' => 'cancel',
            'effective_date' => $subscription->end_date
        ];
        $subscription->save();

        return redirect()->route('client.services.index')
            ->with('success', 'Service scheduled for cancellation at end of billing period');
    }

    private function calculateProration($subscription, $newService)
    {
        $daysRemaining = now()->diffInDays($subscription->end_date);
        $totalDays = $subscription->start_date->diffInDays($subscription->end_date);
        
        $oldAmount = $subscription->productService->price;
        $newAmount = $newService->price;
        
        $proratedRefund = ($oldAmount / $totalDays) * $daysRemaining;
        $proratedCharge = ($newAmount / $totalDays) * $daysRemaining;
        
        return $proratedCharge - $proratedRefund;
    }
}