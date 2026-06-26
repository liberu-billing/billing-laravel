<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
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
])]
class ClientContact extends Model
{
    #[Override]
    protected function casts(): array
    {

        return [
            'is_primary' => 'boolean',
            'can_view_invoices' => 'boolean',
            'can_make_payments' => 'boolean',
            'can_manage_services' => 'boolean',
        ];

    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }
}
