<?php

namespace Lostlink\Messenger;

final class Message
{
    public array|string $body;

    public string $driver;

    /** Driver-specific metadata: routing keys, auth overrides, stream names, etc. */
    public array $attributes;

    public function __construct(array|string $body, string $driver, array $attributes = [])
    {
        $this->body = $body;
        $this->driver = $driver;
        $this->attributes = $attributes;
    }
}
