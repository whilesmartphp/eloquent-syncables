<?php

namespace Whilesmart\Syncables;

use Illuminate\Support\ServiceProvider;

class SyncablesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/syncables.php', 'syncables');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/syncables.php' => config_path('syncables.php'),
        ], 'syncables-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'syncables-migrations');
    }
}
