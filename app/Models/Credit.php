<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'customer_id',
    'amount',
    'description',
    'expiry_date',
])]
class Credit extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    #[Override]
    protected function casts(): array
    {
        return ['expiry_date' => 'datetime'];
    }
}
