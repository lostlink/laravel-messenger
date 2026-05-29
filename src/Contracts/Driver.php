<?php

namespace Lostlink\Messenger\Contracts;

use Lostlink\Messenger\Message;

interface Driver
{
    public function send(Message $message, array $config): void;
}
