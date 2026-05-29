<?php

namespace Lostlink\Messenger\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Messenger;
use Lostlink\Messenger\PendingMessage;
use Lostlink\Messenger\Tests\TestCase;

class PendingMessageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Ensure log driver is enabled so destructor dispatches work
        config(['laravel-messenger.drivers.log.enabled' => true]);
    }

    public function test_destruct_dispatches_message_on_scope_exit(): void
    {
        Log::spy();

        // Create a PendingMessage inside a closure so scope exit triggers __destruct
        (function () {
            new PendingMessage('hello');
        })();

        // Log driver calls Log::info — if we got here without exception the dispatch ran
        Log::shouldHaveReceived('info')->once();
    }

    public function test_builder_methods_carry_through_to_message(): void
    {
        $pending = (new PendingMessage('test-body'))
            ->driver('log')
            ->config(['custom_key' => 'custom_value'])
            ->stream('my-stream')
            ->partitionKey('pk-123')
            ->token('tok-abc')
            ->endpoint('https://example.com/ingest');

        $message = $pending->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('test-body', $message->body);
        $this->assertSame('log', $message->driver);
        $this->assertSame('my-stream', $message->attributes['stream']);
        $this->assertSame('pk-123', $message->attributes['partitionKey']);
        $this->assertSame('tok-abc', $message->attributes['token']);
        $this->assertSame('https://example.com/ingest', $message->attributes['endpoint']);
        $this->assertSame('custom_value', $message->configOverrides['custom_key']);
    }

    public function test_destruct_does_not_propagate_when_driver_throws(): void
    {
        Messenger::extend('throwing-driver', function () {
            return new class implements Driver {
                public function send(Message $message, array $config): void
                {
                    throw new \RuntimeException('driver exploded');
                }
            };
        });

        // Register a minimal config entry so DispatchMessage doesn't bail on 'enabled' check
        config(['laravel-messenger.drivers.throwing-driver' => ['enabled' => true]]);

        // Should not throw — DispatchMessage catches internally and __destruct also has its own catch
        $exceptionPropagated = false;
        try {
            (function () {
                (new PendingMessage('boom'))->driver('throwing-driver');
            })();
        } catch (\Throwable) {
            $exceptionPropagated = true;
        }

        $this->assertFalse($exceptionPropagated, 'Exception propagated out of __destruct but it should have been swallowed');
    }

    public function test_destruct_logs_error_when_dispatch_itself_throws(): void
    {
        Log::spy();

        // Bind a broken DispatchMessage that throws before catching
        $this->app->bind(\Lostlink\Messenger\Actions\DispatchMessage::class, function () {
            return new class {
                public function __invoke(\Lostlink\Messenger\Message $message): never
                {
                    throw new \RuntimeException('dispatch exploded: ' . $message->driver);
                }
            };
        });

        // __destruct's try/catch should catch this and call Log::error
        (function () {
            (new PendingMessage('boom'))->driver('log');
        })();

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'dispatch exploded'));
    }

    public function test_to_message_produces_correct_message(): void
    {
        $pending = (new PendingMessage(['key' => 'value']))
            ->driver('log')
            ->config(['override' => true]);

        $message = $pending->toMessage();

        $this->assertSame(['key' => 'value'], $message->body);
        $this->assertSame('log', $message->driver);
        $this->assertSame([], $message->attributes);
        $this->assertSame(['override' => true], $message->configOverrides);
    }
}
