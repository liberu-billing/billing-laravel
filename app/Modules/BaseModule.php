<?php

declare(strict_types=1);

namespace App\Modules;

use App\Modules\Contracts\ModuleInterface;
use App\Modules\Events\ModuleDisabled;
use App\Modules\Events\ModuleEnabled;
use App\Modules\Events\ModuleInstalled;
use App\Modules\Events\ModuleUninstalled;
use App\Modules\Traits\Configurable;
use App\Modules\Traits\HasModuleHooks;
use Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use ReflectionClass;

abstract class BaseModule implements ModuleInterface
{
    use Configurable, HasModuleHooks;

    protected string $name;

    protected string $version;

    protected string $description;

    protected array $dependencies = [];

    protected array $config = [];

    public function __construct()
    {
        $this->loadModuleInfo();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function isEnabled(): bool
    {
        return Cache::get("module.{$this->name}.enabled", false);
    }

    public function enable(): void
    {
        $this->executeHook('before_enable');
        Cache::put("module.{$this->name}.enabled", true);
        $this->onEnable();
        $this->executeHook('after_enable');
        Event::dispatch(new ModuleEnabled($this->name, $this));
    }

    public function disable(): void
    {
        $this->executeHook('before_disable');
        Cache::put("module.{$this->name}.enabled", false);
        $this->onDisable();
        $this->executeHook('after_disable');
        Event::dispatch(new ModuleDisabled($this->name, $this));
    }

    public function install(): void
    {
        $this->executeHook('before_install');
        $this->runMigrations();
        $this->publishAssets();
        $this->onInstall();
        $this->enable();
        $this->executeHook('after_install');
        Event::dispatch(new ModuleInstalled($this->name, $this));
    }

    public function uninstall(): void
    {
        $this->executeHook('before_uninstall');
        $this->disable();
        $this->rollbackMigrations();
        $this->removeAssets();
        $this->onUninstall();
        $this->executeHook('after_uninstall');
        Event::dispatch(new ModuleUninstalled($this->name, $this));
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function loadModuleInfo(): void
    {
        $modulePath = $this->getModulePath();
        $moduleInfoPath = $modulePath.'/module.json';

        if (File::exists($moduleInfoPath)) {
            $moduleInfo = json_decode(File::get($moduleInfoPath), true);

            $this->name = $moduleInfo['name'] ?? class_basename($this);
            $this->version = $moduleInfo['version'] ?? '1.0.0';
            $this->description = $moduleInfo['description'] ?? '';
            $this->dependencies = $moduleInfo['dependencies'] ?? [];
            $this->config = $moduleInfo['config'] ?? [];
        }
    }

    protected function getModulePath(): string
    {
        $reflection = new ReflectionClass($this);

        return dirname($reflection->getFileName());
    }

    protected function runMigrations(): void
    {
        $migrationsPath = $this->getModulePath().'/database/migrations';

        if (! File::exists($migrationsPath)) {
            return;
        }

        // Guard against path traversal
        $resolvedPath = realpath('app/Modules/'.$this->name.'/database/migrations');
        $resolvedBase = realpath(app_path('Modules'));

        if ($resolvedPath === false || $resolvedBase === false || ! str_starts_with($resolvedPath, $resolvedBase)) {
            return;
        }

        Artisan::call('migrate', [
            '--path' => 'app/Modules/'.$this->name.'/database/migrations',
            '--force' => true,
        ]);
    }

    protected function rollbackMigrations(): void
    {
        // Modules may implement specific rollback logic if needed
    }

    protected function publishAssets(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => strtolower($this->name).'-assets',
            '--force' => true,
        ]);
    }

    protected function removeAssets(): void
    {
        $assetsPath = public_path("modules/{$this->name}");
        if (File::exists($assetsPath)) {
            File::deleteDirectory($assetsPath);
        }
    }

    protected function onEnable(): void {}

    protected function onDisable(): void {}

    protected function onInstall(): void {}

    protected function onUninstall(): void {}
}
