<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'webhook_endpoint_id',
    'event_type',
    'payload',
    'status',
    'attempts',
    'last_error',
    'sent_at',
    'next_retry_at',
])]
class WebhookEvent extends Model
{
    #[\Override]
    protected function casts(): array

    {

        return [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    }

    public function webhookEndpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'next_retry_at' => null,
        ]);
    }

    public function markAsFailed(string $error, int $retryIntervalSeconds = 60): void
    {
        $this->update([
            'status' => 'failed',
            'last_error' => $error,
            'attempts' => $this->attempts + 1,
            'next_retry_at' => now()->addSeconds($retryIntervalSeconds),
        ]);
    }

    public function shouldRetry(int $maxRetries): bool
    {
        return $this->attempts < $maxRetries;
    }
}
