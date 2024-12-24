

<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'payment_id',
        'invoice_id', 
        'customer_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'notes'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}