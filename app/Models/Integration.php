<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'user_id',
    'provider',
    'token',
    'refresh_token',
    'expires_at',
    'scopes',
    'settings',
])]
#[\Illuminate\Database\Eloquent\Attributes\Hidden([
    'token',
    'refresh_token',
])]
class Integration extends Model
{
    #[\Override]
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