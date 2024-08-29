<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'invoice_id',
        'payment_gateway_id',
        'payment_date',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'refund_status',
        'refunded_amount',
        'affiliate_id',
        'affiliate_commission',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function isRefundable()
    {
        return $this->refund_status === 'none' || $this->refund_status === 'partial';
    }

    public function getRemainingRefundableAmount()
    {
        return $this->amount - ($this->refunded_amount ?? 0);
    }
}