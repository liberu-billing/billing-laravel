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
        'refund_reason',
        'reconciliation_status',
        'reconciliation_notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'refunded_amount' => 'float',
        'payment_date' => 'datetime',
    ];

    public function getReconciliationStatusBadgeAttribute()
    {
        return match($this->reconciliation_status) {
            'reconciled' => '<span class="badge badge-success">Reconciled</span>',
            'unmatched' => '<span class="badge badge-warning">Unmatched</span>',
            'discrepancy' => '<span class="badge badge-danger">Discrepancy</span>',
            'failed' => '<span class="badge badge-danger">Failed</span>',
            default => '<span class="badge badge-secondary">Pending</span>',
        };
    }

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

    public function processRefund(float $amount, string $reason = null)
    {
        if (!$this->isRefundable()) {
            throw new \Exception('This payment is not eligible for refund');
        }

        if ($amount > $this->getRemainingRefundableAmount()) {
            throw new \Exception('Refund amount exceeds remaining refundable amount');
        }

        $this->refunded_amount = ($this->refunded_amount ?? 0) + $amount;
        $this->refund_status = $this->refunded_amount >= $this->amount ? 'full' : 'partial';
        $this->refund_reason = $reason;
        $this->save();

        return true;
    }

    public function getFormattedAmount()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getFormattedRefundedAmount()
    {
        return number_format($this->refunded_amount ?? 0, 2) . ' ' . $this->currency;
    }

    public function getRefundStatusBadgeAttribute()
    {
        return match($this->refund_status) {
            'none' => '<span class="badge badge-danger">No Refund</span>',
            'partial' => '<span class="badge badge-warning">Partial Refund</span>',
            'full' => '<span class="badge badge-success">Full Refund</span>',
            default => '<span class="badge badge-secondary">Unknown</span>',
        };
    }
}