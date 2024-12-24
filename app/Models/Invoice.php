<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\InvoiceGenerated;
use App\Services\CurrencyService;
use App\Traits\HasTeam;
use Illuminate\Support\Facades\Mail;

class Invoice extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'currency',
        'status',
        'discount_id',
        'discount_amount',
        'invoice_template_id',
    ];
    
    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->items->sum('total_price');
    }

    public function getFinalTotalAttribute()
    {
        return $this->subtotal - ($this->discount_amount ?? 0);
    }
    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    public function sendInvoiceEmail()
    {
        Mail::to($this->customer->email)->send(new InvoiceGenerated($this));
    }

    public function convertAmountTo($targetCurrency)
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->convert(
            $this->total_amount,
            $this->currency,
            $targetCurrency
        );
    }

    public function getFormattedAmount()
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }
}
