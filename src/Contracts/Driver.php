<?php

namespace Lostlink\Messenger\Contracts;

use Lostlink\Messenger\PendingMessage;

interface Driver
{
    public function send(PendingMessage $message): Driver;
}
