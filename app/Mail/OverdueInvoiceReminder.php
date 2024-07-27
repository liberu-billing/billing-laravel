<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class OverdueInvoiceReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue Invoice Reminder',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue-invoice-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}