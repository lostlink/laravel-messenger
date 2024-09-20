<?php

namespace Lostlink\Messenger;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MessengerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //        Collection::make(glob(__DIR__.'Drivers/*.php'))
        //            ->mapWithKeys(static fn ($path) => [$path => pathinfo($path, PATHINFO_FILENAME)])
        //            ->each(static function ($driver) {
        //                $this->app->alias('Lostlink\\Messenger\\Drivers\\'.$driver, $driver);
        //            });

        $this->mergeConfigFrom(
            __DIR__.'/config/laravel-messenger.php', 'laravel-messenger'
        );
    }

    public function boot(): void
    {
        Collection::make(glob(__DIR__.'Macros/Collection/*.php'))
            ->mapWithKeys(static fn ($path) => [$path => pathinfo($path, PATHINFO_FILENAME)])
            ->reject(static fn ($macro) => Collection::hasMacro($macro))
            ->each(static function ($macro) {
                $class = 'Lostlink\\Messenger\\Macros\\Collection\\'.$macro;
                Collection::macro(Str::camel($macro), app($class)());
            });

        $this->publishes([
            __DIR__.'/config/laravel-messenger.php' => config_path('laravel-messenger.php'),
        ]);

    }
}
