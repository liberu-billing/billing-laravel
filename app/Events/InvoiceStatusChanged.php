<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $status;

    public function __construct(Invoice $invoice, string $status)
    {
        $this->invoice = $invoice;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new Channel('invoices.' . $this->invoice->id);
    }

    public function broadcastWith()
    {
        return [
            'invoice_id' => $this->invoice->id,
            'status' => $this->status,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}