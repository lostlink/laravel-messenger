<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Support\Facades\Log as LaravelLog;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\PendingMessage;

class Log implements Driver
{
    public ?string $message = null;

    public bool $status = false;

    public function send(PendingMessage $message): Driver
    {
        try {
            LaravelLog::info($message->body);

            $this->status = true;

        } catch (\Exception $e) {

            $this->status = false;

            $this->message = $e->getMessage();

        }

        return $this;
    }
}
