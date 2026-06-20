<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'exemption_number',
    'reason',
    'expiry_date',
    'is_active',
    'documentation_path',
])]
class TaxExemption extends Model
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isValid(): bool
    {
        return $this->is_active &&
               ($this->expiry_date === null || $this->expiry_date->isFuture());
    }
}
