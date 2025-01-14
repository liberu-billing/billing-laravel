<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    private const MAX_DEPTH = 5; // Reduced from 10 to be more conservative
    private array $processedCurrencies = [];
    private static $isProcessing = false;
    
    public function convert(float $amount, string $from, string $to): float 
    {
        if (self::$isProcessing) {
            throw new \RuntimeException("Recursive currency conversion detected");
        }
        
        try {
            self::$isProcessing = true;
            $this->processedCurrencies = [];
            
            return $this->calculateRate($amount, $from, $to, 0);
        } finally {
            self::$isProcessing = false;
        }
    }

    /**
     * Calculate exchange rate with depth tracking
     */
    private function calculateRate(float $amount, string $from, string $to, int $depth): float
    {
        // Prevent infinite recursion
        if ($depth >= self::MAX_DEPTH) {
            throw new \RuntimeException("Maximum currency conversion depth reached");
        }

        // Prevent circular references
        $key = "{$from}-{$to}";
        if (isset($this->processedCurrencies[$key])) {
            throw new \RuntimeException("Circular reference detected in currency conversion");
        }
        $this->processedCurrencies[$key] = true;

        // Cache key for rate
        $cacheKey = "currency_rate_{$from}_{$to}";

        // Try to get direct conversion rate from cache
        if ($rate = Cache::get($cacheKey)) {
            return $amount * $rate;
        }

        // Your rate calculation logic here
        // Make sure to implement proper error handling
        
        // Clean up processed currencies after calculation
        unset($this->processedCurrencies[$key]);
        
        return $amount * $rate;
    }

    /**
     * Clear memory
     */
    public function __destruct()
    {
        $this->processedCurrencies = [];
    }
}