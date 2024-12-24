

<?php

namespace App\Services;

use App\Models\TaxRate;
use App\Models\Invoice;

class TaxService
{
    public function calculateTax(Invoice $invoice)
    {
        $customer = $invoice->customer;
        $taxRates = TaxRate::where('team_id', $invoice->team_id)
            ->where('is_active', true)
            ->where('country', $customer->country)
            ->where(function ($query) use ($customer) {
                $query->whereNull('state')
                    ->orWhere('state', $customer->state);
            })
            ->get();

        $totalTax = 0;
        foreach ($invoice->items as $item) {
            $applicableRate = $taxRates->first(function ($rate) use ($item) {
                return $rate->service_type === $item->productService->type;
            });

            if ($applicableRate) {
                $totalTax += $item->total_price * ($applicableRate->rate / 100);
            }
        }

        return round($totalTax, 2);
    }
}