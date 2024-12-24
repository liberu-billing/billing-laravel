<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function hasPermissionTo($permission): bool 
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public static function defaultRoles(): array
    {
        return [
            'super_admin' => 'Full access to all features',
            'admin' => 'Administrative access with some restrictions',
            'staff' => 'Standard staff access',
            'client' => 'Client access with limited permissions',
            'free' => 'Basic access for free users'
        ];
    }
}
