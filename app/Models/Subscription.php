<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_service_id',
        'start_date',
        'end_date',
        'renewal_period',
        'status',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    
    public function productService()
    {
        return $this->belongsTo(Products_Service::class, 'product_service_id');
    }
}
