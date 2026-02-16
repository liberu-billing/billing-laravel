<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'team_id',
        'created_by',
        'name',
        'subject',
        'content',
        'recipient_filters',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'started_sending_at',
        'completed_at',
    ];

    protected $casts = [
        'recipient_filters' => 'array',
        'scheduled_at' => 'datetime',
        'started_sending_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markAsSending(): void
    {
        $this->update([
            'status' => 'sending',
            'started_sending_at' => now(),
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'completed_at' => now(),
        ]);
    }

    public function incrementSent(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        $total = $this->sent_count + $this->failed_count;
        return round(($total / $this->total_recipients) * 100, 2);
    }
}
