

<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(
        string $action,
        $entity = null,
        array $oldValues = null,
        array $newValues = null
    ): void {
        $user = Auth::user();
        
        $data = [
            'user_id' => $user?->id,
            'team_id' => $user?->currentTeam?->id,
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        if ($entity) {
            $data['entity_type'] = get_class($entity);
            $data['entity_id'] = $entity->id;
        }

        if ($oldValues) {
            $data['old_values'] = $oldValues;
        }

        if ($newValues) {
            $data['new_values'] = $newValues;
        }

        AuditLog::create($data);
    }
}