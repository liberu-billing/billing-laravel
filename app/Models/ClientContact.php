<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
        'is_primary',
        'can_view_invoices',
        'can_make_payments',
        'can_manage_services',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'can_view_invoices' => 'boolean',
        'can_make_payments' => 'boolean',
        'can_manage_services' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
