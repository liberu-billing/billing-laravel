<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    private $handlingError = false;
    private $recursionDepth = 0;
    private const MAX_RECURSION_DEPTH = 3;

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->recursionDepth >= self::MAX_RECURSION_DEPTH) {
                return;
            }

            $this->recursionDepth++;
            
            try {
                if ($e instanceof \Error || $e instanceof \Exception) {
                    $message = $e->getMessage();
                    $trace = $e->getTraceAsString();
                    
                    // Log detailed information about the error
                    logger()->error('Error detected', [
                        'message' => $message,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $trace,
                        'previous' => $e->getPrevious() ? get_class($e->getPrevious()) : null,
                        'recursion_depth' => $this->recursionDepth
                    ]);

                    // Check for common container binding issues
                    if (str_contains($trace, 'Container.php')) {
                        logger()->error('Possible container binding issue detected', [
                            'bindings' => app()->getBindings()
                        ]);
                    }
                }
            } finally {
                $this->recursionDepth--;
            }
        });
    }
}
