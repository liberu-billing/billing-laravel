<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Override;

#[Fillable([
    'team_id',
    'fee_type',
    'fee_amount',
    'grace_period_days',
    'max_fee_amount',
    'is_compound',
    'frequency',
])]
class LateFeeConfiguration extends Model
{
    use HasTeam;

    #[Override]
    protected function casts(): array
    {

        return [
            'is_compound' => 'boolean',
            'fee_amount' => 'decimal:2',
            'max_fee_amount' => 'decimal:2',
            'grace_period_days' => 'integer',
        ];

    }

    public static function getFrequencyOptions(): array
    {
        return [
            'one-time' => 'One Time',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    public static function getFeeTypeOptions(): array
    {
        return [
            'percentage' => 'Percentage',
            'fixed' => 'Fixed Amount',
        ];
    }

    public function validate(): void
    {
        if ($this->fee_type === 'percentage' && $this->fee_amount > 100) {
            throw new InvalidArgumentException('Percentage fee cannot exceed 100%');
        }

        if ($this->fee_amount < 0) {
            throw new InvalidArgumentException('Fee amount cannot be negative');
        }

        if ($this->grace_period_days < 0) {
            throw new InvalidArgumentException('Grace period days cannot be negative');
        }
    }

    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::saving(
            static function ($config): void {
                $config->validate();
            }
        );
    }
}
