

<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSubscriptionBilling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BillingService $billingService)
    {
        Subscription::query()
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->get()
            ->each(function ($subscription) use ($billingService) {
                if ($subscription->needsBilling()) {
                    $invoice = $billingService->generateInvoice($subscription);
                    $billingService->processAutomaticPayment($invoice);
                    $subscription->renew();
                }
            });
    }
}