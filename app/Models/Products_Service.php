<?php

namespace App\Models;

use Exception;
use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products_Service extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'type',
        'pricing_model',
        'custom_pricing_data',
    ];

    protected $casts = [
        'custom_pricing_data' => 'array',
    ];

    public function getPriceAttribute()
    {
        return $this->base_price;
    }

    public function invoiceItems()
    {
        return $this->hasMany(Invoice_Item::class, 'product_service_id');
    }

    public function usageRecords()
    {
        return $this->hasManyThrough(UsageRecord::class, Subscription::class);
    }

    public function getUsageMetrics()
    {
        if ($this->pricing_model !== 'usage_based') {
            return [];
        }
        
        return array_keys($this->custom_pricing_data['usage_config'] ?? []);
    }

    public function recordUsage($subscriptionId, $metric, $quantity)
    {
        if (!in_array($metric, $this->getUsageMetrics())) {
            throw new Exception("Invalid usage metric: {$metric}");
        }

        return UsageRecord::create([
            'subscription_id' => $subscriptionId,
            'metric_name' => $metric,
            'quantity' => $quantity,
            'recorded_at' => now(),
        ]);
    }
}
