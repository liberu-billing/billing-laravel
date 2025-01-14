

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;

class ContainerServiceProvider extends ServiceProvider
{
    private array $resolving = [];
    private const MAX_DEPTH = 50;

    public function register()
    {
        $this->app->beforeResolving(function ($abstract) {
            if (!isset($this->resolving[$abstract])) {
                $this->resolving[$abstract] = 0;
            }
            
            $this->resolving[$abstract]++;
            
            if ($this->resolving[$abstract] > self::MAX_DEPTH) {
                throw new \RuntimeException("Possible circular dependency detected while resolving {$abstract}");
            }
        });

        $this->app->afterResolving(function ($abstract) {
            if (isset($this->resolving[$abstract])) {
                $this->resolving[$abstract]--;
            }
        });
    }
}