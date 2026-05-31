<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'code',
    'description', 
    'price',
    'currency',
    'features',
    'is_active',
    'trial_days'
])]
class SubscriptionPlan extends Model
{
    use HasFactory;

    #[\Override]
    protected function casts(): array

    {

        return [
        'features' => 'array',
        'is_active' => 'boolean',
        'trial_days' => 'integer',
        'price' => 'decimal:2'
    ];

    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    protected function formattedPrice(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn(): string => number_format($this->price, 2) . ' ' . $this->currency);
    }
}