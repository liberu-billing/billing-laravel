<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'code',
    'name',
    'exchange_rate',
])]
class Currency extends Model
{
    use HasFactory;

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'currency', 'code');
    }

    public function invoiceItems()
    {
        return $this->hasMany(Invoice_Item::class, 'currency', 'code');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'currency', 'code');
    }
}
