<?php

namespace Lostlink\Messenger;

class Messenger
{
    public static function send(): PendingMessage
    {
        return new PendingMessage(...func_get_args());
    }
}
