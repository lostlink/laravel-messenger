<?php

namespace Lostlink\Messenger\Contracts;

use Lostlink\Messenger\Message;

interface SupportsBatch
{
    /** @param Message[] $messages */
    public function sendMany(array $messages, array $config): void;
}
