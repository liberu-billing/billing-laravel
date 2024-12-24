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
        'domain_name',
        'domain_registrar',
        'domain_expiration_date',
        'scheduled_change',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'domain_expiration_date' => 'datetime',
        'scheduled_change' => 'array'
    ];

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
        $renewalPeriod = $this->getRenewalPeriod();
        $this->end_date = Carbon::parse($this->end_date)->add($renewalPeriod);
        $this->status = 'active';
        $this->save();
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function isDomainActive()
    {
        return $this->domain_name && $this->domain_expiration_date && $this->domain_expiration_date->isFuture();
    }

    private function getRenewalPeriod()
    {
        switch ($this->renewal_period) {
            case 'monthly':
                return '1 month';
            case 'quarterly':
                return '3 months';
            case 'semi-annually':
                return '6 months';
            case 'annually':
                return '1 year';
            default:
                return '1 month'; // Default to monthly if not specified
        }
    }
}
