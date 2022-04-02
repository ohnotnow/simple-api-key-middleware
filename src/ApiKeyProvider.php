<?php

namespace Ohffs\SimpleApiKeyMiddleware;

use Illuminate\Support\ServiceProvider;
use Ohffs\SimpleApiKeyMiddleware\Commands\GenerateApiKey;
use Ohffs\SimpleApiKeyMiddleware\Commands\RemoveApiKey;

class ApiKeyProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app('router')->aliasMiddleware('simple-api-key', \Ohffs\SimpleApiKeyMiddleware\ApiKeyMiddleware::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->publishes([
            __DIR__.'/config/api_keys.php' => config_path('api_keys.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateApiKey::class,
                RemoveApiKey::class,
            ]);
        }
    }
}
