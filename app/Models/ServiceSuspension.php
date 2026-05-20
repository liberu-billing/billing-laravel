<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSuspension extends Model
{
    protected $fillable = [
        'subscription_id',
        'invoice_id',
        'reason',
        'notes',
        'suspended_at',
        'unsuspended_at',
        'suspended_by',
        'unsuspended_by',
        'is_active',
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
        'unsuspended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function unsuspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unsuspended_by');
    }

    public function unsuspend(?int $userId = null): void
    {
        $this->update([
            'unsuspended_at' => now(),
            'unsuspended_by' => $userId,
            'is_active' => false,
        ]);
    }

    public function isSuspended(): bool
    {
        return $this->is_active && $this->unsuspended_at === null;
    }
}
