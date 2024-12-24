<?php

namespace App\Models;

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
        // This method can be implemented to calculate the price based on the pricing model and custom data
        // For now, we'll return the base price
        return $this->base_price;
    }

    public function invoiceItems()
    {
        return $this->hasMany(Invoice_Item::class, 'product_service_id');
    }
}
