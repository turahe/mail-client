<?php

namespace Turahe\MailClient\Providers;

use Illuminate\Support\ServiceProvider;

class MailClientServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'./../../config/config.php' => $this->app->configPath('mail-client.php'),
        ], 'config');

        if ($this->app instanceof \Illuminate\Foundation\Application) {
            $databasePath = __DIR__.'./../../database/migrations';
            $this->loadMigrationsFrom($databasePath);
        }

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'./../../config/config.php', 'mail-client');

    }
}
