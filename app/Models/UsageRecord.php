<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'metric_name',
        'quantity',
        'recorded_at',
        'processed',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'processed' => 'boolean',
        'quantity' => 'decimal:2'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}