<?php

namespace Lostlink\Messenger\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Lostlink\Messenger\Actions\DispatchMessage;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Events\MessageFailed;
use Lostlink\Messenger\Events\MessageSending;
use Lostlink\Messenger\Events\MessageSent;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Messenger;
use Lostlink\Messenger\Tests\TestCase;

class DispatchMessageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Register a no-op spy driver available by default for tests
        Messenger::extend('spy', function () {
            return new class implements Driver {
                public bool $called = false;

                public function send(Message $message, array $config): void
                {
                    $this->called = true;
                }
            };
        });

        config(['laravel-messenger.drivers.spy' => ['enabled' => true]]);
    }

    public function test_message_sending_event_fires_before_driver_send(): void
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        $message = new Message('hello', 'spy');
        $dispatcher = app(DispatchMessage::class);
        $dispatcher($message);

        Event::assertDispatched(MessageSending::class);
    }

    public function test_message_sent_event_fires_after_successful_send(): void
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        $message = new Message('hello', 'spy');
        $result = app(DispatchMessage::class)($message);

        $this->assertTrue($result->status);
        Event::assertDispatched(MessageSent::class);
    }

    public function test_message_failed_event_fires_when_driver_throws(): void
    {
        Event::fake([MessageFailed::class, MessageSending::class]);

        Messenger::extend('failing', function () {
            return new class implements Driver {
                public function send(Message $message, array $config): void
                {
                    throw new \RuntimeException('transport error');
                }
            };
        });
        config(['laravel-messenger.drivers.failing' => ['enabled' => true]]);

        $message = new Message('hello', 'failing');
        $result = app(DispatchMessage::class)($message);

        $this->assertFalse($result->status);
        $this->assertNotEmpty($result->errorMessage);
        $this->assertStringContainsString('transport error', $result->errorMessage);

        Event::assertDispatched(MessageFailed::class);
    }

    public function test_disabled_driver_returns_failed_result_and_skips_transport(): void
    {
        Event::fake([MessageSending::class]);

        config(['laravel-messenger.drivers.spy.enabled' => false]);

        $message = new Message('hello', 'spy');
        $result = app(DispatchMessage::class)($message);

        $this->assertFalse($result->status);
        $this->assertNotEmpty($result->errorMessage);

        // MessageSending must NOT have been fired — driver was disabled
        Event::assertNotDispatched(MessageSending::class);
    }

    public function test_rate_limit_key_uses_driver_name(): void
    {
        // Use RateLimiter::spy() to intercept the attempt call
        RateLimiter::spy();

        config([
            'laravel-messenger.drivers.spy.rate_limit.enabled' => true,
            'laravel-messenger.drivers.spy.rate_limit.max_attempts' => 5,
            'laravel-messenger.drivers.spy.rate_limit.decay_seconds' => 60,
        ]);

        $message = new Message('hello', 'spy');
        app(DispatchMessage::class)($message);

        RateLimiter::shouldHaveReceived('attempt')
            ->once()
            ->withArgs(fn ($key) => $key === 'laravel_messenger-spy-rate_limit');
    }

    public function test_rate_limit_exceeded_returns_failed_result(): void
    {
        // Make RateLimiter::attempt always return false (exceeded)
        RateLimiter::shouldReceive('attempt')
            ->andReturn(false);

        config([
            'laravel-messenger.drivers.spy.rate_limit.enabled' => true,
            'laravel-messenger.drivers.spy.rate_limit.max_attempts' => 1,
            'laravel-messenger.drivers.spy.rate_limit.decay_seconds' => 60,
        ]);

        $message = new Message('hello', 'spy');
        $result = app(DispatchMessage::class)($message);

        $this->assertFalse($result->status);
        $this->assertSame('Rate limit exceeded', $result->errorMessage);
    }
}
