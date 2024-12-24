<?php

namespace App\Services;

use App\Models\Products_Service;
use App\Models\UsageRecord;
use Carbon\Carbon;

class PricingService
{
    public function calculatePrice(Products_Service $product, array $options = [])
    {
        $basePrice = $product->base_price;

        switch ($product->pricing_model) {
            case 'fixed':
                return $basePrice;
            case 'tiered':
                return $this->calculateTieredPrice($product, $options);
            case 'usage_based':
                return $this->calculateUsageBasedPrice($product, $options);
            default:
                return $basePrice;
        }
    }

    private function calculateTieredPrice(Products_Service $product, array $options)
    {
        $tiers = $product->custom_pricing_data['tiers'] ?? [];
        $usage = $options['usage'] ?? 0;

        foreach ($tiers as $tier) {
            if ($usage <= $tier['max_usage']) {
                return $tier['price'];
            }
        }

        return end($tiers)['price'] ?? $product->base_price;
    }

    private function calculateUsageBasedPrice(Products_Service $product, array $options)
    {
        $subscriptionId = $options['subscription_id'] ?? null;
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;

        if (!$subscriptionId || !$startDate || !$endDate) {
            return $product->base_price;
        }

        $usageConfig = $product->custom_pricing_data['usage_config'] ?? [];
        $basePrice = $product->base_price;
        $totalPrice = $basePrice;

        // Get usage records for the billing period
        $usage = UsageRecord::where('subscription_id', $subscriptionId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->where('processed', false)
            ->get();

        foreach ($usageConfig as $metric => $pricing) {
            $metricUsage = $usage->where('metric_name', $metric)->sum('quantity');
            
            if ($pricing['type'] === 'per_unit') {
                $totalPrice += $metricUsage * $pricing['rate'];
            } elseif ($pricing['type'] === 'tiered') {
                $totalPrice += $this->calculateTieredMetricPrice($metricUsage, $pricing['tiers']);
            }
        }

        return $totalPrice;
    }

    private function calculateTieredMetricPrice($usage, $tiers)
    {
        $price = 0;
        $remainingUsage = $usage;

        foreach ($tiers as $tier) {
            $tierUsage = min($remainingUsage, $tier['max_usage']);
            $price += $tierUsage * $tier['rate'];
            $remainingUsage -= $tierUsage;

            if ($remainingUsage <= 0) break;
        }

        return $price;
    }
}