<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\ModuleManager;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, fn (): ModuleManager => new ModuleManager);
    }

    public function boot(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
    }
}
