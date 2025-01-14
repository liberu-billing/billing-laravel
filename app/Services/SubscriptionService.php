

<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    use PreventRecursion;

    public function processRenewal($subscription)
    {
        if (!$this->preventRecursion('renewal_' . $subscription->id)) {
            Log::warning('Renewal already in progress for subscription: ' . $subscription->id);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('renewal_' . $subscription->id);
        }
    }

    public function updateSubscriptionStatus($subscription)
    {
        if (!$this->preventRecursion('status_update_' . $subscription->id)) {
            Log::warning('Status update already in progress for subscription: ' . $subscription->id);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('status_update_' . $subscription->id);
        }
    }
}