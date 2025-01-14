

<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class AuditService
{
    use PreventRecursion;

    public function logActivity($user, $action, $details)
    {
        if (!$this->preventRecursion('log_activity_' . md5($action . serialize($details)))) {
            Log::warning('Activity logging already in progress for this action');
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('log_activity_' . md5($action . serialize($details)));
        }
    }

    public function pruneOldRecords($days = 90)
    {
        if (!$this->preventRecursion('prune_audit_logs')) {
            Log::warning('Audit log pruning already in progress');
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('prune_audit_logs');
        }
    }
}