<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    protected $dates = ['start_date', 'end_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function productService()
    {
        return $this->belongsTo(Products_Service::class, 'product_service_id');
    }

    public function renew()
    {
        $this->end_date = Carbon::parse($this->end_date)->add($this->renewal_period);
        $this->save();
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }
}
