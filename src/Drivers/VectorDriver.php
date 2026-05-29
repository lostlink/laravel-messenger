<?php

namespace Lostlink\Messenger\Drivers;

use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Contracts\HasPersistentConnection;
use Lostlink\Messenger\Exceptions\TransportException;
use Lostlink\Messenger\Message;

final class VectorDriver implements Driver, HasPersistentConnection
{
    private mixed $socket = null;

    public function send(Message $message, array $config): void
    {
        $payload = (new NormalizeBody)($message->body) . "\n";

        $host = $config['host'];
        $port = $config['port'];
        $timeout = $config['timeout'] ?? 2;
        $protocol = $config['protocol'] ?? 'tcp';

        if ($protocol === 'udp') {
            if (strlen($payload) > 65000) {
                throw new TransportException(
                    "Vector UDP payload exceeds datagram cap (~65KB); switch to 'tcp' protocol or shrink the payload (current: ".strlen($payload).' bytes)'
                );
            }

            $sock = stream_socket_client("udp://{$host}:{$port}", $errno, $errstr, $timeout);

            if ($sock === false) {
                throw new TransportException("Failed to connect to Vector (udp): {$errstr} ({$errno})");
            }

            try {
                if (fwrite($sock, $payload) === false) {
                    throw new TransportException('Vector UDP write failed');
                }
            } finally {
                fclose($sock);
            }

            return;
        }

        // TCP — non-persistent
        if (($config['persistent'] ?? false) !== true) {
            $sock = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);

            if ($sock === false) {
                throw new TransportException("Failed to connect to Vector (tcp): {$errstr} ({$errno})");
            }

            stream_set_timeout($sock, $timeout);

            try {
                if (fwrite($sock, $payload) === false) {
                    throw new TransportException('Vector TCP write failed');
                }
            } finally {
                fclose($sock);
            }

            return;
        }

        // TCP — persistent connection
        if ($this->socket === null || ! is_resource($this->socket)) {
            $sock = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);

            if ($sock === false) {
                throw new TransportException("Failed to connect to Vector (tcp): {$errstr} ({$errno})");
            }

            $this->socket = $sock;
        }

        stream_set_timeout($this->socket, $timeout);

        if (fwrite($this->socket, $payload) === false) {
            $this->socket = null;
            throw new TransportException('Vector TCP persistent write failed; connection reset');
        }
    }

    public function close(): void
    {
        if ($this->socket !== null && is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
    }
}
