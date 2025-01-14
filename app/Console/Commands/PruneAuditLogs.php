<?php

namespace App\Console\Commands;

use App\Models\AuditLog;

class PruneAuditLogs extends BaseCommand
{
    protected $signature = 'audit:prune {--days=90}';
    protected $description = 'Prune old audit logs';

    public function handle(): int
    {
        return $this->executeWithLock('prune_audit_logs', function() {
            try {
                $days = $this->option('days');
                $count = AuditLog::where('created_at', '<', now()->subDays($days))->delete();
                
                $this->info("Deleted {$count} audit logs older than {$days} days.");
                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error('Error pruning audit logs: ' . $e->getMessage());
                return self::FAILURE;
            }
        });
    }
}