

<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    private $apiKey;
    private $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rates.api_key');
    }

    public function convert($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        return round($amount * $rate, 2);
    }

    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($fromCurrency, $toCurrency) {
            $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$fromCurrency}", [
                'apikey' => $this->apiKey
            ]);
            
            if ($response->successful()) {
                $rates = $response->json()['rates'];
                return $rates[$toCurrency] ?? null;
            }
            
            throw new \Exception('Failed to fetch exchange rate');
        });
    }

    public function getSupportedCurrencies()
    {
        return Currency::all();
    }
}