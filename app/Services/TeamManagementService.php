<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamManagementService
{
    public function assignUserToDefaultTeam(User $user): void
    {
        DB::transaction(
            function () use ($user): void {
                $team = Team::firstOrCreate(
                    ['name' => $user->name . "'s Team"],
                    [
                        'user_id' => $user->id,
                        'personal_team' => true,
                    ]
                );

                if (!$user->belongsToTeam($team)) {
                    $user->teams()->attach(
                        $team,
                        ['role' => 'admin']
                    );
                }

                $user->forceFill(['current_team_id' => $team->id])->save();
            }
        );
    }
}
