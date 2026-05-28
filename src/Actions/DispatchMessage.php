<?php

namespace Lostlink\Messenger\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Lostlink\Messenger\DriverManager;
use Lostlink\Messenger\Events\MessageFailed;
use Lostlink\Messenger\Events\MessageSending;
use Lostlink\Messenger\Events\MessageSent;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Results\DispatchResult;

class DispatchMessage
{
    public function __invoke(Message $message): DispatchResult
    {
        $driverConfig = config("laravel-messenger.drivers.{$message->driver}", []);

        $finalMessage = (new ApplyEnvelope)($message, $driverConfig);

        event(new MessageSending($finalMessage));

        if (Arr::get($driverConfig, 'enabled') === false) {
            return new DispatchResult(false, "Laravel Messenger {$message->driver} driver is disabled", $message->driver, 0);
        }

        try {
            if (Arr::get($driverConfig, 'rate_limit.enabled') && Arr::get($driverConfig, 'rate_limit.max_attempts') != 0) {
                $executed = RateLimiter::attempt(
                    "laravel_messenger-{$message->driver}-rate_limit",
                    (int) Arr::get($driverConfig, 'rate_limit.max_attempts'),
                    function () use ($message, $driverConfig) {
                        app(DriverManager::class)->resolve($message->driver)->send($message, $driverConfig);
                    },
                    (int) Arr::get($driverConfig, 'rate_limit.decay_seconds'),
                );

                if ($executed === false) {
                    return new DispatchResult(false, 'Rate limit exceeded', $message->driver, 0);
                }
            } else {
                app(DriverManager::class)->resolve($message->driver)->send($message, $driverConfig);
            }

            event(new MessageSent($finalMessage, new DispatchResult(true, null, $message->driver, 1)));

            return new DispatchResult(true, null, $message->driver, 1);

        } catch (\Throwable $e) {
            event(new MessageFailed($finalMessage, new DispatchResult(false, $e->getMessage(), $message->driver, 0)));

            return new DispatchResult(false, $e->getMessage(), $message->driver, 0);
        }
    }
}
