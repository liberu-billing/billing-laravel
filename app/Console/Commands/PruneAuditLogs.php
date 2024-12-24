

<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days=90}';
    protected $description = 'Prune old audit logs';

    public function handle(): void
    {
        $days = $this->option('days');
        $count = AuditLog::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Deleted {$count} audit logs older than {$days} days.");
    }
}