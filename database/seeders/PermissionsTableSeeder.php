<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'view_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:38',
                'updated_at' => '2024-08-29 04:45:38',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'view_any_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:38',
                'updated_at' => '2024-08-29 04:45:38',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'create_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:39',
                'updated_at' => '2024-08-29 04:45:39',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'update_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:39',
                'updated_at' => '2024-08-29 04:45:39',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'delete_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:39',
                'updated_at' => '2024-08-29 04:45:39',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'delete_any_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:40',
                'updated_at' => '2024-08-29 04:45:40',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'page_EditProfile',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:40',
                'updated_at' => '2024-08-29 04:45:40',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'page_PersonalAccessTokensPage',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:41',
                'updated_at' => '2024-08-29 04:45:41',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'page_UpdateProfileInformationPage',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 04:45:41',
                'updated_at' => '2024-08-29 04:45:41',
            ),
        ));
        
        
    }
}