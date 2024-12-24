<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;
use App\Models\EmailTemplate;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    protected $template;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->template = EmailTemplate::getTemplate('invoice_generated', $invoice->team_id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template ? 
                $this->parseTemplate($this->template->subject) : 
                'Invoice Generated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dynamic-template',
            with: [
                'content' => $this->template ? 
                    $this->parseTemplate($this->template->body) : 
                    view('emails.invoice-generated', ['invoice' => $this->invoice])->render(),
                'invoice' => $this->invoice,
            ],
        );
    }

    protected function parseTemplate($text)
    {
        $replacements = [
            '{{invoice_number}}' => $this->invoice->invoice_number,
            '{{amount}}' => $this->invoice->total_amount,
            '{{due_date}}' => $this->invoice->due_date->format('Y-m-d'),
            '{{customer_name}}' => $this->invoice->customer->name,
        ];

        return strtr($text, $replacements);
    }

    public function attachments(): array
    {
        return [];
    }
}