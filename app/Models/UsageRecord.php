<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'subscription_id',
    'metric_name',
    'quantity',
    'recorded_at',
    'processed',
])]
class UsageRecord extends Model
{
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {

        return [
            'recorded_at' => 'datetime',
            'processed' => 'boolean',
            'quantity' => 'decimal:2',
        ];

    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
