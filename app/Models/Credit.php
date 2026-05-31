<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'customer_id',
    'amount',
    'description',
    'expiry_date',
])]
class Credit extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    #[\Override]
    protected function casts(): array
    {
        return ['expiry_date' => 'datetime'];
    }
}
