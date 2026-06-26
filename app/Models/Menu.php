<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'url',
    'parent_id',
    'order',
])]
class Menu extends Model
{
    use HasFactory, SoftDeletes;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            __CLASS__,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_id'
        );
    }
}
