<?php

namespace App\Providers;

use App\Modules\ModuleManager;
use App\Modules\ModuleServiceProvider;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the module manager as a singleton
        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        app(PermissionRegistrar::class)->setPermissionsTeamId(1);

    }
}
