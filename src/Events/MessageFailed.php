<?php

namespace Lostlink\Messenger\Events;

use Lostlink\Messenger\Message;
use Lostlink\Messenger\Results\DispatchResult;

class MessageFailed
{
    public Message $message;

    public DispatchResult $result;

    public function __construct(Message $message, DispatchResult $result)
    {
        $this->message = $message;
        $this->result = $result;
    }
}
