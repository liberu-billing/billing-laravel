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
}<?php

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

    public function validate()
    {
        if ($this->fee_type === 'percentage' && $this->fee_amount > 100) {
            throw new \InvalidArgumentException('Percentage fee cannot exceed 100%');
        }

        if ($this->fee_amount < 0) {
            throw new \InvalidArgumentException('Fee amount cannot be negative');
        }

        if ($this->grace_period_days < 0) {
            throw new \InvalidArgumentException('Grace period days cannot be negative');
        }
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($config) {
            $config->validate();
        });
    }
}