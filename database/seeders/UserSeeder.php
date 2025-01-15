<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::firstOrFail();
        setPermissionsTeamId($team->id);
        
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        // $adminUser->assignRole('admin');

        // Create teams for admin and staff users
        $this->createTeamForUser($adminUser);
    }

    private function createTeamForUser($user)
    {
        $team = Team::first();
        $team->users()->attach($user);

        $user->current_team_id = 1;
        $user->save();
    }
}
