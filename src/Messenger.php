<?php

namespace Lostlink\Messenger;

class Messenger
{
    public static function send(): PendingMessage
    {
        return new PendingMessage(...func_get_args());
    }

    public static function extend(string $name, \Closure $factory): void
    {
        app(\Lostlink\Messenger\DriverManager::class)->extend($name, $factory);
    }
}
