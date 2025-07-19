<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailable;
    protected $recipient;
    
    public $tries = 3;
    public $backoff = 300; // 5 minutes

    public function __construct(Mailable $mailable, string $recipient)
    {
        $this->mailable = $mailable;
        $this->recipient = $recipient;
    }

    public function handle()
    {
        try {
            Mail::to($this->recipient)->send($this->mailable);
            
            Log::info('Queued email sent successfully', [
                'recipient' => $this->recipient,
                'mailable_class' => get_class($this->mailable)
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send queued email', [
                'recipient' => $this->recipient,
                'mailable_class' => get_class($this->mailable),
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            throw $e;
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipient,
            'mailable_class' => get_class($this->mailable),
            'error' => $exception->getMessage()
        ]);
    }
}