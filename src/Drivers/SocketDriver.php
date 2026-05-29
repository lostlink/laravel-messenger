<?php

namespace Lostlink\Messenger\Drivers;

use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Exceptions\TransportException;
use Lostlink\Messenger\Message;

final class SocketDriver implements Driver
{
    public function send(Message $message, array $config): void
    {
        $normalized = (new NormalizeBody)($message->body);

        $socket = fsockopen(
            $config['host'],
            $config['port'],
            $errno,
            $errstr,
            $config['timeout'] ?? 30
        );

        if ($socket === false) {
            throw new TransportException("Failed to connect to Socket: {$errstr} ({$errno})");
        }

        fwrite($socket, $normalized . "\n");
        fclose($socket);
    }
}
