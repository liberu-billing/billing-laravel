<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\InvoiceGenerated;
use App\Services\CurrencyService;
use App\Services\AuditLogService;
use App\Traits\HasTeam;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;
    use HasTeam;

    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($invoice) {
            app(AuditLogService::class)->log(
                'invoice_created',
                $invoice,
                null,
                $invoice->toArray()
            );
        });

        static::updated(function ($invoice) {
            app(AuditLogService::class)->log(
                'invoice_updated',
                $invoice,
                $invoice->getOriginal(),
                $invoice->getChanges()
            );
        });

        static::deleted(function ($invoice) {
            app(AuditLogService::class)->log(
                'invoice_deleted',
                $invoice,
                $invoice->toArray(),
                null
            );
        });
    }

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
        'discount_id',
        'discount_amount',
        'invoice_template_id',
        'late_fee_amount',
        'last_late_fee_date',
        'is_recurring',
        'tax_amount',
    ];
    
    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'last_late_fee_date' => 'datetime',
        'late_fee_amount' => 'decimal:2',
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function processPayment(string $paymentMethod, float $amount)
    {
        if ($amount <= 0 || $amount > $this->remaining_amount) {
            throw new \Exception('Invalid payment amount');
        }

        $payment = new Payment([
            'invoice_id' => $this->id,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'currency' => $this->currency,
            'payment_date' => now(),
        ]);

        $paymentGatewayService = app(PaymentGatewayService::class);
        $result = $paymentGatewayService->processPayment($payment);

        if ($result['success']) {
            $payment->transaction_id = $result['transaction_id'];
            $payment->save();

            $this->updateStatus();
            
            return true;
        }

        throw new \Exception($result['message']);
    }

    public function updateStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        
        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partially_paid';
        }
        
        $this->save();
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->payments()->sum('amount');
    }

    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function installments()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
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
        return $this->subtotal + ($this->tax_amount ?? 0) - ($this->discount_amount ?? 0);
    }

    public function calculateTax()
    {
        $taxService = app(TaxService::class);
        return $taxService->calculateTax($this);
    }
    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
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

    public function isOverdue()
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function calculateLateFee()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $config = LateFeeConfiguration::where('team_id', $this->team_id)->first();
        if (!$config) {
            return 0;
        }

        // Check grace period
        $daysOverdue = $this->due_date->diffInDays(now());
        if ($daysOverdue <= $config->grace_period_days) {
            return 0;
        }

        $baseAmount = $config->is_compound ? 
            ($this->total_amount + $this->late_fee_amount) : 
            $this->total_amount;

        $fee = $config->fee_type === 'percentage' ?
            ($baseAmount * ($config->fee_amount / 100)) :
            $config->fee_amount;

        // Apply frequency rules
        if ($this->last_late_fee_date) {
            $daysSinceLastFee = $this->last_late_fee_date->diffInDays(now());
            $fee = match($config->frequency) {
                'one-time' => 0,
                'daily' => $daysSinceLastFee >= 1 ? $fee : 0,
                'weekly' => $daysSinceLastFee >= 7 ? $fee : 0,
                'monthly' => $daysSinceLastFee >= 30 ? $fee : 0,
                default => 0,
            };
        }

        // Check max fee amount
        if ($config->max_fee_amount) {
            $totalFees = $this->late_fee_amount + $fee;
            if ($totalFees > $config->max_fee_amount) {
                $fee = max(0, $config->max_fee_amount - $this->late_fee_amount);
            }
        }

        return round($fee, 2);
    }

    public function applyLateFee()
    {
        $fee = $this->calculateLateFee();
        if ($fee > 0) {
            $this->late_fee_amount += $fee;
            $this->last_late_fee_date = now();
            $this->save();

            // Log the late fee application
            app(AuditLogService::class)->log(
                'late_fee_applied',
                $this,
                ['previous_late_fee' => $this->late_fee_amount - $fee],
                ['new_late_fee' => $this->late_fee_amount]
            );
        }
        return $fee;
    }

    public function getTotalWithLateFeeAttribute()
    {
        return $this->final_total + $this->late_fee_amount;
    }

    public function getFormattedTotalWithLateFeeAttribute()
    {
        return number_format($this->total_with_late_fee, 2) . ' ' . $this->currency;
    }

    public function getRemainingLateFeeAttribute()
    {
        $config = LateFeeConfiguration::where('team_id', $this->team_id)->first();
        if (!$config || !$config->max_fee_amount) {
            return null;
        }
        return max(0, $config->max_fee_amount - $this->late_fee_amount);
    }

    public function recurringConfiguration()
    {
        return $this->hasOne(RecurringBillingConfiguration::class);
    }

    public function setupRecurringBilling($frequency, $billingDay = null)
    {
        if ($this->is_installment) {
            throw new \Exception('Cannot set up recurring billing for an installment invoice');
        }

        $this->update(['is_recurring' => true]);
        
        return $this->recurringConfiguration()->create([
            'frequency' => $frequency,
            'billing_day' => $billingDay,
            'next_billing_date' => $this->calculateNextBillingDate($frequency, $billingDay),
            'is_active' => true
        ]);
    }

    private function calculateNextBillingDate($frequency, $billingDay = null)
    {
        $date = now();
        
        if ($billingDay && $billingDay > $date->day) {
            $date->setDay($billingDay);
        } else {
            $date = match($frequency) {
                'monthly' => $date->addMonth(),
                'quarterly' => $date->addMonths(3),
                'yearly' => $date->addYear(),
                default => $date->addMonth()
            };
            
            if ($billingDay) {
                $date->setDay($billingDay);
            }
        }
        
        return $date;
    }
}
