<?php

declare(strict_types=1);

namespace App\Modules;

use App\Modules\Support\ExternalModuleLoader;
use Filament\PanelRegistry;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        try {
            $this->registerModules();
            $this->registerExternalModules();
        } catch (\Throwable) {
            // Silently skip during initial setup (no DB / cache yet)
        }
    }

    public function boot(): void
    {
        try {
            $this->bootModules();
        } catch (\Throwable) {
            // Silently skip during initial setup (no DB / cache yet)
        }
    }

    /**
     * Register all modules found in the modules directory.
     */
    protected function registerModules(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename((string) $modulePath);
            $this->registerModule($moduleName, $modulePath);
        }
    }

    /**
     * Register a specific module.
     */
    protected function registerModule(string $moduleName, string $modulePath): void
    {
        // Register module service provider if it exists
        $providerPath = $modulePath.'/Providers/'.$moduleName.'ServiceProvider.php';
        if (File::exists($providerPath)) {
            $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }

        // Register module configuration
        $configPath = $modulePath.'/config';
        if (File::exists($configPath)) {
            $configFiles = File::files($configPath);
            foreach ($configFiles as $configFile) {
                $configName = Str::snake($moduleName).'.'.$configFile->getFilenameWithoutExtension();
                $this->mergeConfigFrom($configFile->getPathname(), $configName);
            }
        }

        // Register module routes
        $this->registerModuleRoutes($moduleName, $modulePath);

        // Register module views
        $viewsPath = $modulePath.'/resources/views';
        if (File::exists($viewsPath)) {
            $this->loadViewsFrom($viewsPath, Str::snake($moduleName));
        }

        // Register module translations
        $langPath = $modulePath.'/resources/lang';
        if (File::exists($langPath)) {
            $this->loadTranslationsFrom($langPath, Str::snake($moduleName));
        }

        // Register module migrations
        $migrationsPath = $modulePath.'/database/migrations';
        if (File::exists($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    /**
     * Register module routes.
     */
    protected function registerModuleRoutes(string $moduleName, string $modulePath): void
    {
        $routesPath = $modulePath.'/routes';

        if (! File::exists($routesPath)) {
            return;
        }

        // Web routes
        $webRoutesPath = $routesPath.'/web.php';
        if (File::exists($webRoutesPath)) {
            $this->loadRoutesFrom($webRoutesPath);
        }

        // API routes
        $apiRoutesPath = $routesPath.'/api.php';
        if (File::exists($apiRoutesPath)) {
            $this->loadRoutesFrom($apiRoutesPath);
        }

        // Admin routes (for Filament integration)
        $adminRoutesPath = $routesPath.'/admin.php';
        if (File::exists($adminRoutesPath)) {
            $this->loadRoutesFrom($adminRoutesPath);
        }
    }

    /**
     * Boot all registered modules.
     */
    protected function bootModules(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename((string) $modulePath);
            $this->bootModule($moduleName, $modulePath);
        }
    }

    /**
     * Boot a specific module.
     */
    protected function bootModule(string $moduleName, string $modulePath): void
    {
        $assetsPath = $modulePath.'/resources/assets';
        if (File::exists($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path("modules/{$moduleName}"),
            ], Str::snake($moduleName).'-assets');
        }

        $configPath = $modulePath.'/config';
        if (File::exists($configPath)) {
            $configFiles = File::files($configPath);
            foreach ($configFiles as $configFile) {
                $this->publishes([
                    $configFile->getPathname() => config_path(Str::snake($moduleName).'.'.$configFile->getFilename()),
                ], Str::snake($moduleName).'-config');
            }
        }

        $this->registerModuleFilament($moduleName, $modulePath);
    }

    /**
     * Register a module's Filament resources, pages, and widgets with all panels.
     */
    protected function registerModuleFilament(string $moduleName, string $modulePath): void
    {
        $filamentPath = $modulePath.'/Filament';

        if (! File::isDirectory($filamentPath) || ! class_exists(PanelRegistry::class)) {
            return;
        }

        $namespace = "App\\Modules\\{$moduleName}\\Filament";

        $this->callAfterResolving(PanelRegistry::class, function (PanelRegistry $registry) use ($filamentPath, $namespace): void {
            foreach ($registry->all() as $panel) {
                if (File::isDirectory($filamentPath.'/Resources')) {
                    $panel->discoverResources(
                        in: $filamentPath.'/Resources',
                        for: $namespace.'\\Resources',
                    );
                }

                if (File::isDirectory($filamentPath.'/Pages')) {
                    $panel->discoverPages(
                        in: $filamentPath.'/Pages',
                        for: $namespace.'\\Pages',
                    );
                }

                if (File::isDirectory($filamentPath.'/Widgets')) {
                    $panel->discoverWidgets(
                        in: $filamentPath.'/Widgets',
                        for: $namespace.'\\Widgets',
                    );
                }
            }
        });
    }

    protected function registerExternalModules(): void
    {
        if (! config('modules.load_composer_modules', false) && empty(config('modules.external_paths', []))) {
            return;
        }

        $loader = new ExternalModuleLoader;

        foreach ($loader->load() as $module) {
            $moduleName = $module->getName();
            $this->registerModule($moduleName, dirname((new \ReflectionClass($module))->getFileName()));
        }
    }
}
