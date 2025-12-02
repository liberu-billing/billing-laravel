<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::firstOrFail();
        $defaultTeamId = $team->id;
        // Create base permissions
        $permissions = [
            // User management
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Role management
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            // Permission management
            'view_permissions', 'assign_permissions',
            // Team management
            'view_teams', 'create_teams', 'edit_teams', 'delete_teams',
            // Billing
            'view_billing', 'manage_billing',
            // Settings
            'view_settings', 'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'super_admin' => $permissions,
            'admin' => array_filter($permissions, fn($p) => !in_array($p, ['view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'view_permissions', 'assign_permissions'])),
            'staff' => array_filter($permissions, fn($p) => in_array($p, ['view_users', 'create_users', 'edit_users', 'view_teams', 'create_teams', 'edit_teams', 'view_billing', 'view_settings', 'manage_settings'])),
            'client' => array_filter($permissions, fn($p) => in_array($p, ['view_users', 'view_roles', 'view_permissions', 'view_teams', 'view_billing', 'view_settings'])),
            'free' => ['view_billing']
        ];

        foreach ($roles as $roleName => $rolePermissionNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'team_id' => $defaultTeamId]
            );
        
            // Fetch permission models for this team
            $permissionsModels = Permission::whereIn('name', $rolePermissionNames)
                                           ->get();
        
            $role->syncPermissions($permissionsModels);
        }
    }
}
