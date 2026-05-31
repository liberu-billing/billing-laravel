<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'team_id',
    'name',
    'description',
    'sort_order',
    'is_active',
])]
class PackageGroup extends Model
{
    #[\Override]
    protected function casts(): array

    {

        return [
        'is_active' => 'boolean',
    ];

    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(
            SubscriptionPlan::class,
            'package_group_items',
            'package_group_id',
            'subscription_plan_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }
}
