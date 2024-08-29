<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'status',
        'custom_rates',
    ];

    protected $casts = [
        'custom_rates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getCommissionRate($productId = null, $categoryId = null)
    {
        if ($productId && isset($this->custom_rates['products'][$productId])) {
            return $this->custom_rates['products'][$productId];
        }

        if ($categoryId && isset($this->custom_rates['categories'][$categoryId])) {
            return $this->custom_rates['categories'][$categoryId];
        }

        return $this->commission_rate;
    }
}