<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages;
use App\Http\Middleware\TeamsPermission;
use App\Models\Team;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Jetstream\Features;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors(
                [
                    'primary' => Color::Gray,
                ]
            )
            ->discoverResources(
                in: app_path('Filament/Admin/Resources'),
                for: 'App\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Admin/Pages'),
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets/Home'),
                for: 'App\\Filament\\Admin\\Widgets\\Home'
            )
            ->pages(
                [
                    Dashboard::class,
                    Pages\EditProfile::class,
                ]
            )
            ->widgets(
                [
                    Widgets\AccountWidget::class,
                ]
            )
            ->middleware(
                [
                    EncryptCookies::class,
                    AddQueuedCookiesToResponse::class,
                    StartSession::class,
                    AuthenticateSession::class,
                    ShareErrorsFromSession::class,
                    PreventRequestForgery::class,
                    SubstituteBindings::class,
                    DisableBladeIconComponents::class,
                    DispatchServingFilamentEvent::class,
                ]
            )
            ->authMiddleware(
                [
                    Authenticate::class,
                    TeamsPermission::class,
                ]
            )
            ->plugins(
                [
                    // Roles are global (Shield tenancy disabled: tenant_model = null),
                    // so the Role model has no `team` ownership relationship. Without
                    // scopeToTenant(false) the tenant-scoped admin panel scopes
                    // RoleResource by `team` and throws on every page.
                    FilamentShieldPlugin::make()
                        ->navigationGroup('Administration')
                        ->scopeToTenant(false),
                ]
            );

        if (Features::hasTeamFeatures()) {
            $panel
                ->tenant(
                    Team::class,
                    ownershipRelationship: 'team'
                )
                ->tenantRegistration(Pages\CreateTeam::class)
                ->tenantProfile(Pages\EditTeam::class)
                ->userMenuItems(
                    [
                        Action::make('teamSettings')
                            ->label('Team Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->url(
                                fn (): string => $this->shouldRegisterMenuItem()
                                    ? url(Pages\EditTeam::getUrl())
                                    : url($panel->getPath())
                            ),
                    ]
                );
        }

        return $panel;
    }

    public function boot(): void {}

    public function shouldRegisterMenuItem(): bool
    {
        return auth()->user()?->currentTeam && Filament::hasTenancy() && Filament::getTenant();
    }
}
