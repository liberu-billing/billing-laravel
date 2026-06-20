<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'product_service_id',
    'description',
    'quantity',
    'unit_price',
    'total_price',
    'currency',
])]
#[Table(name: 'invoice_items')]
class Invoice_Item extends Model
{
    use HasTeam;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(Products_Service::class, 'product_service_id');
    }
}
