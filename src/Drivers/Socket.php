<?php

namespace Lostlink\Messenger\Drivers;

class Socket extends Driver
{
    public function handle(): void
    {
        $socket = fsockopen($this->message->config->get('host'), $this->message->config->get('port'), $errno, $errstr, 30);

        if (!$socket) {
            // Handle connection failure
            error_log("Failed to connect to Socket: $errstr ($errno)");
            return;
        }

        fwrite($socket, is_array($this->message->body) ? json_encode($this->message->body) : $this->message->body . "\n");

        fclose($socket);
    }
}
