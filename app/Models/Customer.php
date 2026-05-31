<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'email',
    'phone_number',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
])]
class Customer extends Model
{
    use HasFactory;

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
