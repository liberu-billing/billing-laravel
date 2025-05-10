<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'token',
        'refresh_token',
        'expires_at',
        'scopes',
        'settings',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'settings' => 'array',
        'scopes' => 'array',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}