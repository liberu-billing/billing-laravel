

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\PreventRecursion;

abstract class BaseCommand extends Command
{
    use PreventRecursion;

    protected function executeWithLock(string $lockKey, callable $callback)
    {
        if (!$this->preventRecursion($lockKey)) {
            $this->warn("Command is already running: {$lockKey}");
            return self::FAILURE;
        }

        try {
            return $callback();
        } finally {
            $this->releaseRecursionLock($lockKey);
        }
    }
}