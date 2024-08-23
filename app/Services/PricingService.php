<?php

namespace App\Services;

use App\Models\Products_Service;

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
        // Implement tiered pricing logic
        // Example: $options['tier'] could determine which price tier to use
        $tiers = $product->custom_pricing_data['tiers'] ?? [];
        $selectedTier = $options['tier'] ?? 'default';

        return $tiers[$selectedTier] ?? $product->base_price;
    }

    private function calculateUsageBasedPrice(Products_Service $product, array $options)
    {
        // Implement usage-based pricing logic
        // Example: $options['usage'] could determine the usage amount
        $usageRate = $product->custom_pricing_data['usage_rate'] ?? 0;
        $usage = $options['usage'] ?? 0;

        return $product->base_price + ($usageRate * $usage);
    }
}