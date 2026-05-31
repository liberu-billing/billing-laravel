<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'base_price',
    'markup_type',
    'markup_value',
    'enom_cost',
])]
class Tld extends Model
{
    use HasFactory;

    #[\Override]
    protected function casts(): array

    {

        return [
        'base_price' => 'float',
        'markup_value' => 'float',
        'enom_cost' => 'float',
    ];

    }

    public function calculatePrice()
    {
        if ($this->markup_type === 'percentage') {
            return $this->enom_cost * (1 + $this->markup_value / 100);
        } elseif ($this->markup_type === 'fixed') {
            return $this->enom_cost + $this->markup_value;
        }
        return $this->base_price;
    }
}
