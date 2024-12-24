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
        'price',
        'currency',
        'auto_renew',
        'last_billed_at',
    ];

    protected $dates = ['start_date', 'end_date', 'last_billed_at'];

    protected $casts = [
        'auto_renew' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function productService()
    {
        return $this->belongsTo(Products_Service::class, 'product_service_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function renew()
    {
        if (!$this->auto_renew || $this->status === 'cancelled') {
            return false;
        }

        $renewalPeriod = $this->getRenewalPeriod();
        $this->end_date = Carbon::parse($this->end_date)->add($renewalPeriod);
        $this->last_billed_at = now();
        $this->status = 'active';
        return $this->save();
    }

    public function cancel()
    {
        $this->auto_renew = false;
        $this->status = 'cancelled';
        return $this->save();
    }

    public function suspend()
    {
        $this->status = 'suspended';
        return $this->save();
    }

    public function resume()
    {
        if ($this->status === 'suspended') {
            $this->status = 'active';
            return $this->save();
        }
        return false;
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function needsBilling()
    {
        if (!$this->last_billed_at) {
            return true;
        }

        $nextBillingDate = Carbon::parse($this->last_billed_at)->add($this->getRenewalPeriod());
        return $nextBillingDate->isPast() && $this->isActive();
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
                return '1 month';
        }
    }
}
