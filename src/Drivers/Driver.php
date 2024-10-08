<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Lostlink\Messenger\Contracts\DriverContract;
use Lostlink\Messenger\PendingMessage;

class Driver implements DriverContract
{
    public PendingMessage $message;

    public ?string $errorMessage = null;

    public bool $status = false;

    public function send(PendingMessage $message): self
    {
        $this->message = $message;

        if ($this->message->config->get('enabled') === false) {

            $this->status = false;

            $this->errorMessage = "Laravel Messenger {$this->message->driver} driver is disabled";

            return $this;
        }

        try {
            if (Arr::get($this->message->config, 'rate_limit.enabled') && Arr::get($this->message->config, 'rate_limit.max_attempts') != 0) {
                $executed = RateLimiter::attempt(
                    key: "laravel_messenger-{$this->message->driver}-rate_limit",
                    maxAttempts: (int) Arr::get($this->message->config, 'rate_limit.max_attempts'),
                    callback: function () {
                        $this->handle();
                    },
                    decaySeconds: (int) Arr::get($this->message->config, 'rate_limit.decay_seconds'),
                );

                $this->status = $executed;

                $this->errorMessage = $executed ?: 'Rate limit exceeded';

            } else {

                $this->handle();

                $this->status = true;

            }

        } catch (\Exception $e) {

            $this->status = false;

            $this->errorMessage = $e->getMessage();

        }

        return $this;
    }

    public function handle(): void
    {
        Log::info($this->message->body);
    }
}
