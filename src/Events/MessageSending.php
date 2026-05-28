<?php

namespace Lostlink\Messenger\Events;

use Lostlink\Messenger\Message;

class MessageSending
{
    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
