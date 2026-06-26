<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'user_id',
    'provider',
    'token',
    'refresh_token',
    'expires_at',
    'scopes',
    'settings',
])]
#[Hidden([
    'token',
    'refresh_token',
])]
class Integration extends Model
{
    #[Override]
    protected function casts(): array
    {

        return [
            'expires_at' => 'datetime',
            'settings' => 'array',
            'scopes' => 'array',
        ];

    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
