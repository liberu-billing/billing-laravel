<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class RecurringBillingConfiguration extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'invoice_id',
        'frequency',
        'billing_day',
        'next_billing_date',
        'is_active'
    ];

    protected $casts = [
        'next_billing_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function calculateNextBillingDate()
    {
        $date = now();
        
        if ($this->billing_day && $this->billing_day > $date->day) {
            $date->setDay($this->billing_day);
        } else {
            $date = match($this->frequency) {
                'monthly' => $date->addMonth(),
                'quarterly' => $date->addMonths(3),
                'yearly' => $date->addYear(),
                default => $date->addMonth()
            };
            
            if ($this->billing_day) {
                $date->setDay($this->billing_day);
            }
        }
        
        return $date;
    }
}