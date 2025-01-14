

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SafePackageDiscover extends Command
{
    protected $signature = 'package:safe-discover';
    protected $description = 'Safely run package discovery with loop protection';

    public function handle()
    {
        $lockKey = 'package_discovery_lock';
        
        if (Cache::has($lockKey)) {
            $this->error('Package discovery is already running');
            return Command::FAILURE;
        }

        Cache::put($lockKey, true, 60);

        try {
            $this->call('package:discover', ['--ansi' => true]);
            $this->info('Package discovery completed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Package discovery failed: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            Cache::forget($lockKey);
        }
    }
}