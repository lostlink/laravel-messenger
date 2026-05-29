<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Support\Facades\Log;
use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Message;

final class LogDriver implements Driver
{
    public function send(Message $message, array $config): void
    {
        $normalized = (new NormalizeBody)($message->body);

        Log::info($normalized);
    }
}
