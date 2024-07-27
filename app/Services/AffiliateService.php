<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Affiliate;

class AffiliateService
{
    public function processAffiliateReward(Payment $payment)
    {
        $user = $payment->invoice->customer->user;
        $referrer = $user->referrer;

        if ($referrer && $referrer->status === 'active') {
            $commissionAmount = $payment->amount * ($referrer->commission_rate / 100);

            $payment->update([
                'affiliate_id' => $referrer->id,
                'affiliate_commission' => $commissionAmount,
            ]);

            // Here you can add logic to credit the affiliate's account or create a separate transaction
        }
    }
}