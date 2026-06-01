<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'country',
    'state',
    'rate',
    'service_type',
    'is_active',
    'team_id',
    'threshold_amount',
    'threshold_rate',
    'effective_date',
    'expiry_date',
    'tax_category',
    'description',
])]
class TaxRate extends Model
{
    use HasFactory;
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {

        return [
            'rate' => 'decimal:2',
            'threshold_amount' => 'decimal:2',
            'threshold_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];

    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function isValid(): bool
    {
        $now = now();

        return $this->is_active &&
            ($this->effective_date === null || $this->effective_date <= $now) &&
            ($this->expiry_date === null || $this->expiry_date >= $now);
    }

    #[Scope]
    protected function active($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function getEffectiveRate($amount)
    {
        if ($this->threshold_amount && $amount > $this->threshold_amount) {
            return $this->threshold_rate ?? $this->rate;
        }

        return $this->rate;
    }
}
