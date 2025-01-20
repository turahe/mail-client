<?php

namespace Turahe\MailClient;

use Illuminate\Support\ServiceProvider;

class MailClientServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        //        $this->mergeConfigFrom(__DIR__.'./../config/mail-client.php', 'mail-client.php');

        if ($this->app instanceof \Illuminate\Foundation\Application) {
            $databasePath = __DIR__.'/../database/migrations';
            $this->loadMigrationsFrom($databasePath);

            $this->publishes(
                [
                    __DIR__.'./../config/mail-client.php' => config_path('mail-client.php'),
                ],
                'config'
            );
        }

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mail-client.php', 'mail-client');

    }
}
