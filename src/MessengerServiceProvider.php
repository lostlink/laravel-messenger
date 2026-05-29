<?php

namespace Lostlink\Messenger;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MessengerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/laravel-messenger.php', 'laravel-messenger'
        );

        $this->app->singleton(\Lostlink\Messenger\DriverManager::class);
    }

    public function boot(): void
    {
        Collection::make(glob(__DIR__.'/Macros/Collection/*.php'))
            ->mapWithKeys(static fn ($path) => [$path => pathinfo($path, PATHINFO_FILENAME)])
            ->reject(static fn ($macro) => Collection::hasMacro($macro))
            ->each(static function ($macro) {
                $class = 'Lostlink\\Messenger\\Macros\\Collection\\'.$macro;
                Collection::macro(Str::camel($macro), app($class)());
            });

        $this->app->terminating(function () {
            $this->app->make(\Lostlink\Messenger\DriverManager::class)->closeAll();
        });

        $this->publishes([
            __DIR__.'/config/laravel-messenger.php' => config_path('laravel-messenger.php'),
        ], ['laravel-messenger-config', 'messenger-config']);
    }
}
