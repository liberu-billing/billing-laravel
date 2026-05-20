<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackageGroup extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
