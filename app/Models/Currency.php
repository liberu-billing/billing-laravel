<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'exchange_rate',
])]
class Currency extends Model
{
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'currency', 'code');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(Invoice_Item::class, 'currency', 'code');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'currency', 'code');
    }
}
