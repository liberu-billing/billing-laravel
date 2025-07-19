<?php

namespace App\Exceptions;

use Error;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    private $handlingError = false;

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Prevent recursive error handling
            if ($this->handlingError) {
                return;
            }
            
            $this->handlingError = true;
            
            try {
                if ($e instanceof Error) {
                    if (str_contains($e->getMessage(), 'Maximum call stack size') || 
                        str_contains($e->getMessage(), 'Container.php line 1048')) {
                        logger()->error('Recursion or stack overflow detected', [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } finally {
                $this->handlingError = false;
            }
        });
    }
}
