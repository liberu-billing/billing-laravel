<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'subscription_id',
    'metric_name',
    'quantity',
    'recorded_at',
    'processed',
])]
class UsageRecord extends Model
{#[\Override]
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'processed' => 'boolean',
            'quantity' => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
