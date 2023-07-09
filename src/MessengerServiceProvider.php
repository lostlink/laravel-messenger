<?php

namespace Lostlink\Messenger;

use Illuminate\Support\ServiceProvider;

class MessengerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias("Lostlink\\Messenger\\Drivers\\Kinesis", "Kinesis");

        $this->mergeConfigFrom(
            __DIR__ . '/config/laravel-messenger.php', 'laravel-messenger'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/laravel-messenger.php' => config_path('laravel-messenger.php'),
        ]);

    }
}
