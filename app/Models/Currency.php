<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'exchange_rate',
    ];

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