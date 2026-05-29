<?php

namespace Lostlink\Messenger;

use Illuminate\Support\Facades\Log;
use Lostlink\Messenger\Actions\DispatchMessage;

class PendingMessage
{
    private string $driverName;
    private array $configOverrides = [];
    private array $attributes = [];

    public function __construct(private array|string $body)
    {
        $this->driverName = config('laravel-messenger.default', 'log');
    }

    public function driver(string $driver): static
    {
        $this->driverName = $driver;
        return $this;
    }

    public function config(array $config): static
    {
        $this->configOverrides = array_merge($this->configOverrides, $config);
        return $this;
    }

    public function stream(string $value): static
    {
        $this->attributes['stream'] = $value;
        return $this;
    }

    public function partitionKey(string $value): static
    {
        $this->attributes['partitionKey'] = $value;
        return $this;
    }

    public function token(string $value): static
    {
        $this->attributes['token'] = $value;
        return $this;
    }

    public function endpoint(string $value): static
    {
        $this->attributes['endpoint'] = $value;
        return $this;
    }

    public function toMessage(): Message
    {
        return new Message($this->body, $this->driverName, $this->attributes, $this->configOverrides);
    }

    public function __destruct()
    {
        try {
            app(DispatchMessage::class)($this->toMessage());
        } catch (\Throwable $e) {
            Log::error('[Messenger] Dispatch failed: '.$e->getMessage());
        }
    }
}
