<?php

namespace Lostlink\Messenger;

final class Message
{
    public array|string $body;

    public string $driver;

    /** Driver-specific metadata: routing keys, auth overrides, stream names, etc. */
    public array $attributes;

    /** Per-message config overrides merged on top of the driver's global config. */
    public array $configOverrides;

    public function __construct(array|string $body, string $driver, array $attributes = [], array $configOverrides = [])
    {
        $this->body = $body;
        $this->driver = $driver;
        $this->attributes = $attributes;
        $this->configOverrides = $configOverrides;
    }
}
