<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    private const MAX_COMMAND_DEPTH = 5;
    private array $runningCommands = [];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Prevent recursive command execution
        $this->preventRecursiveScheduling(function() use ($schedule) {
            // Add your scheduled commands here
        });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }

    /**
     * Prevent recursive scheduling of commands
     */
    private function preventRecursiveScheduling(callable $callback): void
    {
        $lockKey = 'console_kernel_scheduling_lock';
        
        if (Cache::has($lockKey)) {
            return;
        }

        try {
            Cache::put($lockKey, true, 60); // 60 seconds lock
            $callback();
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Track command execution depth
     */
    protected function runCommand($command, array $parameters = [], $output = null)
    {
        $commandKey = get_class($command);
        
        if (!isset($this->runningCommands[$commandKey])) {
            $this->runningCommands[$commandKey] = 0;
        }
        
        $this->runningCommands[$commandKey]++;
        
        if ($this->runningCommands[$commandKey] > self::MAX_COMMAND_DEPTH) {
            throw new \RuntimeException("Maximum command execution depth reached for {$commandKey}");
        }
        
        try {
            return parent::runCommand($command, $parameters, $output);
        } finally {
            $this->runningCommands[$commandKey]--;
        }
    }
}

