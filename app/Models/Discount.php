<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'name',
    'description',
    'type', // percentage or fixed
    'value',
    'currency',
    'start_date',
    'end_date',
    'max_uses',
    'used_count',
    'is_active',
])]
class Discount extends Model
{
    use HasFactory;
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {

        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];

    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isValid(): bool
    {
        return $this->is_active &&
            $this->start_date <= now() &&
            $this->end_date >= now() &&
            ($this->max_uses === null || $this->used_count < $this->max_uses);
    }
}
