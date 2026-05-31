<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'invoice_id',
    'product_service_id',
    'description',
    'quantity',
    'unit_price',
    'total_price',
    'currency',
])]
class Invoice_Item extends Model
{
    use HasFactory;
    use HasTeam;

    protected $table = 'invoice_items';
    
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function productService()
    {
        return $this->belongsTo(Products_Service::class, 'product_service_id');
    }
}
