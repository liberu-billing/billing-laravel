

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class PaymentPlan extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'invoice_id',
        'total_installments',
        'installment_amount',
        'frequency',
        'start_date',
        'next_due_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'next_due_date' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installments()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }
}