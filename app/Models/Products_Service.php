<?php

namespace App\Models;

use Exception;
use App\Traits\HasTeam;
use Database\Factories\ProductsServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'description',
    'base_price',
    'type',
    'pricing_model',
    'custom_pricing_data',
])]
#[\Illuminate\Database\Eloquent\Attributes\Table(name: 'products_services')]
class Products_Service extends Model
{
    use HasFactory;
    use HasTeam;

    protected static function newFactory(): ProductsServiceFactory
    {
        return ProductsServiceFactory::new();
    }

    #[\Override]
    protected function casts(): array

    {

        return [
        'custom_pricing_data' => 'array',
    ];

    }

    protected function price(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn() => $this->base_price);
    }

    public function invoiceItems()
    {
        return $this->hasMany(Invoice_Item::class, 'product_service_id');
    }

    public function usageRecords()
    {
        return $this->hasManyThrough(UsageRecord::class, Subscription::class);
    }

    public function getUsageMetrics(): array
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
