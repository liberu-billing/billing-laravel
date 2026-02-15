<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'team_id',
        'url',
        'secret',
        'events',
        'is_active',
        'description',
        'max_retries',
        'retry_interval',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function isSubscribedTo(string $eventType): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (empty($this->events)) {
            return true; // Subscribe to all events if none specified
        }

        return in_array($eventType, $this->events);
    }
}
