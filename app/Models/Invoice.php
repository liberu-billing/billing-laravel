<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Mail\InvoiceGenerated;
use Illuminate\Support\Facades\Mail;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sendInvoiceEmail()
    {
        Mail::to($this->customer->email)->send(new InvoiceGenerated($this));
    }
}
