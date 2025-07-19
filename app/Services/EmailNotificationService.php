<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendEmailNotification;

class EmailNotificationService
{
    protected $maxRetries = 3;
    protected $retryDelay = 300; // 5 minutes

    public function send(Mailable $mailable, string $recipient)
    {
        try {
            Queue::push(new SendEmailNotification($mailable, $recipient));
            
            Log::info('Email notification queued', [
                'recipient' => $recipient,
                'mailable_class' => get_class($mailable)
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to queue email notification', [
                'recipient' => $recipient,
                'mailable_class' => get_class($mailable),
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function sendNow(Mailable $mailable, string $recipient)
    {
        $attempts = 0;
        
        while ($attempts < $this->maxRetries) {
            try {
                Mail::to($recipient)->send($mailable);
                
                Log::info('Email notification sent successfully', [
                    'recipient' => $recipient,
                    'mailable_class' => get_class($mailable),
                    'attempt' => $attempts + 1
                ]);
                
                return true;
            } catch (Exception $e) {
                $attempts++;
                
                Log::warning('Email sending failed, retrying...', [
                    'recipient' => $recipient,
                    'mailable_class' => get_class($mailable),
                    'attempt' => $attempts,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempts >= $this->maxRetries) {
                    Log::error('Email sending failed after max retries', [
                        'recipient' => $recipient,
                        'mailable_class' => get_class($mailable),
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
                
                sleep($this->retryDelay);
            }
        }
        
        return false;
    }
}