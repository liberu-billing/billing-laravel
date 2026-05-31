<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'quote_id',
    'description',
    'quantity',
    'unit_price',
    'total',
    'sort_order',
])]
class QuoteItem extends Model
{
    #[\Override]
    protected function casts(): array

    {

        return [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    }

    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (QuoteItem $item): void {
            $item->total = $item->quantity * $item->unit_price;
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
