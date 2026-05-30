<?php

namespace App\Livewire;

use App\Actions\Jetstream\CreateTeam as CreateTeamAction;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;

class CreateTeam extends CreateTeamForm
{
    public function createTeam(): void
    {
        $this->validate();

        $team = app(CreateTeamAction::class)->create(
            Auth::user(),
            ['name' => $this->state['name']]
        );

        redirect()->route('filament.pages.edit-team', ['team' => $team]);
    }
}
