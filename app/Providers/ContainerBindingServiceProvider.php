

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ContainerBindingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->beforeResolving(function ($abstract, $parameters, $app) {
            if (is_string($abstract)) {
                $key = "resolving_{$abstract}";
                if ($app->bound($key)) {
                    throw new \RuntimeException("Circular dependency detected while resolving {$abstract}");
                }
                $app->bind($key, function() {});
            }
        });

        $this->app->afterResolving(function ($abstract, $resolved, $app) {
            if (is_string($abstract)) {
                $key = "resolving_{$abstract}";
                if ($app->bound($key)) {
                    $app->unbind($key);
                }
            }
        });
    }
}