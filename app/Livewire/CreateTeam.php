<?php

namespace App\Livewire;

use App\Actions\Jetstream\CreateTeam as CreateTeamAction;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;

class CreateTeam extends CreateTeamForm
{
    protected array $rules = [
        'state.name' => 'required|string|min:1|max:255',
    ];

    protected array $validationAttributes = [
        'state.name' => 'name',
    ];

    #[\Override]
    public function createTeam(\Laravel\Jetstream\Contracts\CreatesTeams $creator): void
    {
        $this->validate();

        $team = app(CreateTeamAction::class)->create(
            Auth::user(),
            ['name' => $this->state['name']]
        );

        redirect()->route('filament.pages.edit-team', ['team' => $team]);
    }
}
