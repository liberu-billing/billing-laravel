

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'exemption_number',
        'reason',
        'expiry_date',
        'is_active',
        'documentation_path'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isValid()
    {
        return $this->is_active && 
               ($this->expiry_date === null || $this->expiry_date->isFuture());
    }
}