

<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait PreventRecursion
{
    protected function preventRecursion(string $key, int $timeout = 60): bool
    {
        $lockKey = "recursion_lock_{$key}";
        
        if (Cache::has($lockKey)) {
            return false;
        }

        Cache::put($lockKey, true, $timeout);
        return true;
    }

    protected function releaseRecursionLock(string $key): void
    {
        Cache::forget("recursion_lock_{$key}");
    }
}