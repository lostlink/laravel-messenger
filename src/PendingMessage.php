<?php

namespace Lostlink\Messenger;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PendingMessage
{
    public string $body;

    public string $driver;

    public array|Collection $config;

    public function __construct(string $body)
    {
        $this->body = $body;
        $this->driver = config("laravel-messenger.default");
        $this->config = collect(config("laravel-messenger.drivers.{$this->driver}"));
    }

    public function __call(string $method, $args): PendingMessage
    {
        $this->$method = collect($args)->first();

        return $this;
    }

    public function driver(string $driver): PendingMessage
    {
        if ($driver != config("laravel-messenger.default")) {
            $this->driver = $driver;
            $this->config = collect(config("laravel-messenger.drivers.{$this->driver}"));
        }

        return $this;
    }

    public function config(array $config): PendingMessage
    {
        $this->config = $this->config->merge($config);

        return $this;
    }

    public function __destruct()
    {
        $result = app(config("laravel-messenger.drivers.{$this->driver}.class"))->send($this);

        if ($result->status === false) {
            Log::error($result->message);
        }
    }
}
