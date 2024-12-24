<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\InvoiceGenerated;
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
        'parent_invoice_id',
        'is_installment',
    ];
    
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentPlan()
    {
        return $this->hasOne(PaymentPlan::class);
    }

    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function installments()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    public function sendInvoiceEmail()
    {
        Mail::to($this->customer->email)->send(new InvoiceGenerated($this));
    }

    public function createPaymentPlan($totalInstallments, $frequency = 'monthly')
    {
        if ($this->is_installment) {
            throw new \Exception('Cannot create payment plan for an installment invoice');
        }

        $installmentAmount = round($this->total_amount / $totalInstallments, 2);
        $startDate = now();

        return PaymentPlan::create([
            'invoice_id' => $this->id,
            'total_installments' => $totalInstallments,
            'installment_amount' => $installmentAmount,
            'frequency' => $frequency,
            'start_date' => $startDate,
            'next_due_date' => $this->calculateNextDueDate($startDate, $frequency),
            'status' => 'active',
        ]);
    }

    private function calculateNextDueDate($date, $frequency)
    {
        return match($frequency) {
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            default => $date->addMonth(),
        };
    }
}
