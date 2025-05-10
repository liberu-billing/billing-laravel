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
            $product = $payment->invoice->items->first()->product;
            $commissionRate = $referrer->getCommissionRate($product->id, $product->category_id);
            $commissionAmount = $payment->amount * ($commissionRate / 100);

            $payment->update([
                'affiliate_id' => $referrer->id,
                'affiliate_commission' => $commissionAmount,
            ]);

            $referrer->increment('total_earnings', $commissionAmount);

            // Create a transaction record for the affiliate
            $referrer->transactions()->create([
                'amount' => $commissionAmount,
                'type' => 'commission',
                'description' => "Commission for payment #{$payment->id}",
            ]);
        }
    }
}