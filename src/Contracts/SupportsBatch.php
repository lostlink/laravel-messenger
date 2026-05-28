<?php

namespace Lostlink\Messenger\Contracts;

use Lostlink\Messenger\Message;

interface SupportsBatch
{
    public function sendMany(array $messages, array $config): void;
}
