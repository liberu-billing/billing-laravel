<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'expiry_date',
    ];

    protected $dates = ['expiry_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}