<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'description',
    'price',
    'currency',
    'features',
    'is_active',
    'trial_days',
])]
class SubscriptionPlan extends Model
{
    #[\Override]
    protected function casts(): array
    {

        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'trial_days' => 'integer',
            'price' => 'decimal:2',
        ];

    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(get: fn (): string => number_format($this->price, 2).' '.$this->currency);
    }
}
