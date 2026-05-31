<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'invoice_id',
    'customer_id', 
    'status',
    'reason',
    'description',
    'resolution_notes',
    'resolved_at',
    'resolved_by'
])]
class InvoiceDispute extends Model
{
    use HasFactory;
    use HasTeam;

    #[\Override]
    protected function casts(): array

    {

        return [
        'resolved_at' => 'datetime'
    ];

    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages()
    {
        return $this->hasMany(DisputeMessage::class);
    }
}