<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'user_id',
    'name',
    'criteria',
    'share_token',
])]
class SavedSearch extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
