<?php

declare(strict_types=1);

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
    use \Illuminate\Foundation\Queue\Queueable;
    
    public $tries = 3;
    public $backoff = 300; // 5 minutes

    public function __construct(protected \Illuminate\Mail\Mailable $mailable, protected string $recipient)
    {
    }

    public function handle(): void
    {
        try {
            Mail::to($this->recipient)->send($this->mailable);
            
            Log::info('Queued email sent successfully', [
                'recipient' => $this->recipient,
                'mailable_class' => $this->mailable::class
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send queued email', [
                'recipient' => $this->recipient,
                'mailable_class' => $this->mailable::class,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipient,
            'mailable_class' => $this->mailable::class,
            'error' => $exception->getMessage()
        ]);
    }
}
