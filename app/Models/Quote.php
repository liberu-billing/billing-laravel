<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    protected $fillable = [
        'team_id',
        'customer_id',
        'quote_number',
        'title',
        'status',
        'valid_until',
        'subtotal',
        'tax_amount',
        'total',
        'currency',
        'notes',
        'terms',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'declined_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Quote $quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = 'QUO-' . strtoupper(Str::random(8));
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status !== 'accepted';
    }

    public function canBeConverted(): bool
    {
        return $this->status === 'accepted';
    }
}
