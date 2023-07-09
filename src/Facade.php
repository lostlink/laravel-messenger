<?php

namespace Lostlink\Messenger;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Messenger::class;
    }
}
