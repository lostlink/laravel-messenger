<?php

namespace Lostlink\Messenger;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Lostlink\Messenger\Exceptions\ConfigMalFormedException;
use Lostlink\Messenger\Exceptions\ConfigNotFoundException;
use Lostlink\Messenger\Exceptions\DriverClassNotFoundException;

class PendingMessage
{
    public string $driver;

    public array|Collection $config;

    public function __construct(public string|array $body)
    {
        $this->driver = config('laravel-messenger.default');
        $this->config = collect(config("laravel-messenger.drivers.{$this->driver}"));

        $this->validateConfig();
    }

    public function __call(string $method, $args): PendingMessage
    {
        $this->$method = collect($args)->first();

        return $this;
    }

    public function driver(string $driver): PendingMessage
    {
        if ($driver != config('laravel-messenger.default')) {
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
        $result = app($this->config->get('class'))->send($this);

        if ($result->status === false) {
            Log::error($result->message);
        }
    }

    private function validateConfig(): void
    {
        if ($this->config->isEmpty()) {
            throw new ConfigNotFoundException("Config for driver \"{$this->driver}\" not found");
        }

        if (is_null($this->config->get('class'))) {
            throw new ConfigMalFormedException("Config for driver \"{$this->driver}\" is missing the required class key");
        }

        if (! class_exists($this->config->get('class'))) {
            throw new DriverClassNotFoundException("Class for driver \"{$this->driver}\" not found");
        }
    }
}
