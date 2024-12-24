

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class LateFeeConfiguration extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'team_id',
        'fee_type',
        'fee_amount',
        'grace_period_days',
        'max_fee_amount',
        'is_compound',
        'frequency',
    ];

    protected $casts = [
        'is_compound' => 'boolean',
        'fee_amount' => 'decimal:2',
        'max_fee_amount' => 'decimal:2',
        'grace_period_days' => 'integer',
    ];

    public static function getFrequencyOptions()
    {
        return [
            'one-time' => 'One Time',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly'
        ];
    }

    public static function getFeeTypeOptions()
    {
        return [
            'percentage' => 'Percentage',
            'fixed' => 'Fixed Amount'
        ];
    }
}