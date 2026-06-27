<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\Teams\Pages\CreateTeam;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamResourceTest extends TestCase
{
    use RefreshDatabase;

    private function bootAdminPanel(User $user): void
    {
        $this->actingAs($user);

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        $panel->boot();
        Filament::setTenant($user->currentTeam);
    }

    public function test_admin_can_create_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->bootAdminPanel($user);

        Livewire::test(CreateTeam::class)
            ->fillForm([
                'name' => 'Acme Corp',
                'user_id' => $user->id,
                'personal_team' => false,
                'is_default_for_registration' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teams', [
            'name' => 'Acme Corp',
            'user_id' => $user->id,
        ]);
    }

    public function test_setting_default_for_registration_clears_previous_default(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->bootAdminPanel($user);

        $first = Team::factory()->create([
            'name' => 'First default',
            'user_id' => $user->id,
            'is_default_for_registration' => true,
        ]);

        Livewire::test(CreateTeam::class)
            ->fillForm([
                'name' => 'Second default',
                'user_id' => $user->id,
                'personal_team' => false,
                'is_default_for_registration' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $second = Team::where('name', 'Second default')->firstOrFail();

        $this->assertTrue($second->fresh()->is_default_for_registration);
        $this->assertFalse($first->fresh()->is_default_for_registration);
        $this->assertSame($second->id, Team::defaultForRegistration()?->id);
    }
}
