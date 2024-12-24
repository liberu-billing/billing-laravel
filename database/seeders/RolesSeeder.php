<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $client = Role::firstOrCreate(['name' => 'client']);
        $free = Role::firstOrCreate(['name' => 'free']);

        // Get all permissions
        $permissions = Permission::all();

        // Assign permissions to roles
        $superAdmin->syncPermissions($permissions);
        
        $adminPermissions = $permissions->filter(function ($permission) {
            return !str_contains($permission->name, ['role', 'permission']);
        });
        $admin->syncPermissions($adminPermissions);

        $staffPermissions = $permissions->filter(function ($permission) {
            return str_contains($permission->name, ['view', 'create', 'update']);
        });
        $staff->syncPermissions($staffPermissions);

        $clientPermissions = $permissions->filter(function ($permission) {
            return str_contains($permission->name, ['view']);
        });
        $client->syncPermissions($clientPermissions);
        
        $freePermissions = $permissions->filter(function ($permission) {
            return str_contains($permission->name, ['view']);
        });
        $free->syncPermissions($freePermissions);
    }
}
