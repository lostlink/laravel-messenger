<?php

namespace Lostlink\Messenger\Tests\Feature;

use Lostlink\Messenger\Drivers\SocketDriver;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Tests\TestCase;

class SocketDriverTest extends TestCase
{
    /**
     * Start a loopback TCP server on a random port, send one message via SocketDriver,
     * accept the connection, read what was written, and verify the newline terminator.
     */
    private function runWithServer(Message $message): string
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
        $this->assertNotFalse($server, "Could not start loopback server: {$errstr} ({$errno})");

        $name = stream_socket_get_name($server, false);
        [, $port] = explode(':', $name);

        $config = [
            'host' => '127.0.0.1',
            'port' => (int) $port,
            'timeout' => 5,
        ];

        $driver = new SocketDriver();
        $driver->send($message, $config);

        // Accept the single connection the driver opened
        $conn = stream_socket_accept($server, 5);
        $this->assertNotFalse($conn, 'Server did not receive a connection');

        $received = '';
        while (!feof($conn)) {
            $chunk = fread($conn, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $received .= $chunk;
        }

        fclose($conn);
        fclose($server);

        return $received;
    }

    public function test_string_body_is_terminated_with_newline(): void
    {
        $message = new Message('hello world', 'socket');

        $received = $this->runWithServer($message);

        $this->assertStringEndsWith("\n", $received);
        $this->assertStringContainsString('hello world', $received);
    }

    public function test_array_body_is_terminated_with_newline(): void
    {
        $message = new Message(['event' => 'test', 'value' => 42], 'socket');

        $received = $this->runWithServer($message);

        $this->assertStringEndsWith("\n", $received);

        // The body should have been JSON-encoded
        $decoded = json_decode(rtrim($received, "\n"), true);
        $this->assertIsArray($decoded);
        $this->assertSame('test', $decoded['event']);
        $this->assertSame(42, $decoded['value']);
    }
}
