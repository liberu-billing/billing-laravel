<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

#[\Illuminate\Console\Attributes\Description('Prune old audit logs')]
#[\Illuminate\Console\Attributes\Signature('audit:prune {--days=90}')]
class PruneAuditLogs extends Command
{
    public function handle(): void
    {
        $days = $this->option('days');
        $count = AuditLog::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Deleted {$count} audit logs older than {$days} days.");
    }
}