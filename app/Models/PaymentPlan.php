<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_id',
    'total_installments',
    'installment_amount',
    'frequency',
    'start_date',
    'next_due_date',
    'status',
])]
class PaymentPlan extends Model
{
    use HasTeam;

    #[\Override]
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'next_due_date' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }
}
