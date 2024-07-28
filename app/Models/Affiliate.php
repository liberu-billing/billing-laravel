<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'status',
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
}