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
                'name' => 'view_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:33',
                'updated_at' => '2024-08-29 08:44:33',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'view_any_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'create_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'update_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'restore_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'restore_any_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'replicate_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'reorder_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:34',
                'updated_at' => '2024-08-29 08:44:34',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'delete_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:35',
                'updated_at' => '2024-08-29 08:44:35',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'delete_any_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:35',
                'updated_at' => '2024-08-29 08:44:35',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'force_delete_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:35',
                'updated_at' => '2024-08-29 08:44:35',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'force_delete_any_affiliate',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:35',
                'updated_at' => '2024-08-29 08:44:35',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'view_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'view_any_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'create_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'update_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'restore_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'restore_any_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:36',
                'updated_at' => '2024-08-29 08:44:36',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'replicate_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'reorder_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'delete_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'delete_any_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'force_delete_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'force_delete_any_hosting::account',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:37',
                'updated_at' => '2024-08-29 08:44:37',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'view_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'view_any_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'create_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'update_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'restore_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'restore_any_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'replicate_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:38',
                'updated_at' => '2024-08-29 08:44:38',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'reorder_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'delete_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            33 => 
            array (
                'id' => 34,
                'name' => 'delete_any_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            34 => 
            array (
                'id' => 35,
                'name' => 'force_delete_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            35 => 
            array (
                'id' => 36,
                'name' => 'force_delete_any_partial::payment',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            36 => 
            array (
                'id' => 37,
                'name' => 'view_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:39',
                'updated_at' => '2024-08-29 08:44:39',
            ),
            37 => 
            array (
                'id' => 38,
                'name' => 'view_any_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            38 => 
            array (
                'id' => 39,
                'name' => 'create_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            39 => 
            array (
                'id' => 40,
                'name' => 'update_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            40 => 
            array (
                'id' => 41,
                'name' => 'restore_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            41 => 
            array (
                'id' => 42,
                'name' => 'restore_any_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            42 => 
            array (
                'id' => 43,
                'name' => 'replicate_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            43 => 
            array (
                'id' => 44,
                'name' => 'reorder_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:40',
                'updated_at' => '2024-08-29 08:44:40',
            ),
            44 => 
            array (
                'id' => 45,
                'name' => 'delete_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            45 => 
            array (
                'id' => 46,
                'name' => 'delete_any_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            46 => 
            array (
                'id' => 47,
                'name' => 'force_delete_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            47 => 
            array (
                'id' => 48,
                'name' => 'force_delete_any_products::service',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            48 => 
            array (
                'id' => 49,
                'name' => 'view_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            49 => 
            array (
                'id' => 50,
                'name' => 'view_any_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            50 => 
            array (
                'id' => 51,
                'name' => 'create_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:41',
                'updated_at' => '2024-08-29 08:44:41',
            ),
            51 => 
            array (
                'id' => 52,
                'name' => 'update_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:42',
                'updated_at' => '2024-08-29 08:44:42',
            ),
            52 => 
            array (
                'id' => 53,
                'name' => 'restore_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:42',
                'updated_at' => '2024-08-29 08:44:42',
            ),
            53 => 
            array (
                'id' => 54,
                'name' => 'restore_any_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:42',
                'updated_at' => '2024-08-29 08:44:42',
            ),
            54 => 
            array (
                'id' => 55,
                'name' => 'replicate_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:42',
                'updated_at' => '2024-08-29 08:44:42',
            ),
            55 => 
            array (
                'id' => 56,
                'name' => 'reorder_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:42',
                'updated_at' => '2024-08-29 08:44:42',
            ),
            56 => 
            array (
                'id' => 57,
                'name' => 'delete_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:43',
                'updated_at' => '2024-08-29 08:44:43',
            ),
            57 => 
            array (
                'id' => 58,
                'name' => 'delete_any_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:43',
                'updated_at' => '2024-08-29 08:44:43',
            ),
            58 => 
            array (
                'id' => 59,
                'name' => 'force_delete_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:43',
                'updated_at' => '2024-08-29 08:44:43',
            ),
            59 => 
            array (
                'id' => 60,
                'name' => 'force_delete_any_refund',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:43',
                'updated_at' => '2024-08-29 08:44:43',
            ),
            60 => 
            array (
                'id' => 61,
                'name' => 'view_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            61 => 
            array (
                'id' => 62,
                'name' => 'view_any_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            62 => 
            array (
                'id' => 63,
                'name' => 'create_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            63 => 
            array (
                'id' => 64,
                'name' => 'update_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            64 => 
            array (
                'id' => 65,
                'name' => 'delete_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            65 => 
            array (
                'id' => 66,
                'name' => 'delete_any_role',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            66 => 
            array (
                'id' => 67,
                'name' => 'view_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:44',
                'updated_at' => '2024-08-29 08:44:44',
            ),
            67 => 
            array (
                'id' => 68,
                'name' => 'view_any_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            68 => 
            array (
                'id' => 69,
                'name' => 'create_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            69 => 
            array (
                'id' => 70,
                'name' => 'update_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            70 => 
            array (
                'id' => 71,
                'name' => 'restore_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            71 => 
            array (
                'id' => 72,
                'name' => 'restore_any_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            72 => 
            array (
                'id' => 73,
                'name' => 'replicate_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            73 => 
            array (
                'id' => 74,
                'name' => 'reorder_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:45',
                'updated_at' => '2024-08-29 08:44:45',
            ),
            74 => 
            array (
                'id' => 75,
                'name' => 'delete_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:46',
                'updated_at' => '2024-08-29 08:44:46',
            ),
            75 => 
            array (
                'id' => 76,
                'name' => 'delete_any_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:46',
                'updated_at' => '2024-08-29 08:44:46',
            ),
            76 => 
            array (
                'id' => 77,
                'name' => 'force_delete_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:46',
                'updated_at' => '2024-08-29 08:44:46',
            ),
            77 => 
            array (
                'id' => 78,
                'name' => 'force_delete_any_user',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:46',
                'updated_at' => '2024-08-29 08:44:46',
            ),
            78 => 
            array (
                'id' => 79,
                'name' => 'page_EditProfile',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:46',
                'updated_at' => '2024-08-29 08:44:46',
            ),
            79 => 
            array (
                'id' => 80,
                'name' => 'page_PersonalAccessTokensPage',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:47',
                'updated_at' => '2024-08-29 08:44:47',
            ),
            80 => 
            array (
                'id' => 81,
                'name' => 'page_UpdateProfileInformationPage',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 08:44:47',
                'updated_at' => '2024-08-29 08:44:47',
            ),
        ));
        
        
    }
}