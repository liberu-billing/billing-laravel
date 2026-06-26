<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $exchange_rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Invoice_Item> $invoiceItems
 * @property-read Collection<int, Payment> $payments
 */
#[Fillable([
    'code',
    'name',
    'exchange_rate',
])]
class Currency extends Model
{
    public function invoices(): HasMany
    {
        return $this->hasMany(
            Invoice::class,
            'currency',
            'code'
        );
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(
            Invoice_Item::class,
            'currency',
            'code'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'currency',
            'code'
        );
    }
}
