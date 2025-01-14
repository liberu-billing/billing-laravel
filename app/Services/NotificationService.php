

<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    use PreventRecursion;

    public function sendBulkNotifications($users, $message)
    {
        $batchId = md5(serialize($users) . $message . time());
        if (!$this->preventRecursion('bulk_notify_' . $batchId)) {
            Log::warning('Bulk notification already in progress for batch: ' . $batchId);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('bulk_notify_' . $batchId);
        }
    }
}