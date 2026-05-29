<?php

namespace Lostlink\Messenger\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Lostlink\Messenger\Actions\DispatchMessage;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Jobs\SendMessageJob;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Messenger;
use Lostlink\Messenger\Tests\TestCase;

class QueueDriverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'laravel-messenger.drivers.queue' => [
                'enabled' => true,
                'class' => \Lostlink\Messenger\Drivers\QueueDriver::class,
                'driver' => 'log',
                'connection' => null,
                'queue' => null,
                'delay' => 0,
                'rate_limit' => ['enabled' => false, 'max_attempts' => 0, 'decay_seconds' => 60],
                'envelope' => ['uuid' => false, 'timestamp' => false],
            ],
        ]);
    }

    public function test_queue_driver_pushes_send_message_job(): void
    {
        Queue::fake();

        $message = new Message('queued message', 'queue');
        app(DispatchMessage::class)($message);

        Queue::assertPushed(SendMessageJob::class);
    }

    public function test_job_payload_carries_correct_target_driver(): void
    {
        Queue::fake();

        config(['laravel-messenger.drivers.queue.driver' => 'log']);

        $message = new Message('queued message', 'queue');
        app(DispatchMessage::class)($message);

        Queue::assertPushed(SendMessageJob::class, function (SendMessageJob $job) {
            return $job->message->driver === 'log';
        });
    }

    public function test_send_message_job_handle_calls_driver_send(): void
    {
        // Track whether send() was called on our spy driver
        $tracker = new \stdClass();
        $tracker->called = false;

        Messenger::extend('spy-target', function () use ($tracker) {
            return new class ($tracker) implements Driver {
                public function __construct(private \stdClass $tracker) {}

                public function send(Message $message, array $config): void
                {
                    $this->tracker->called = true;
                }
            };
        });

        config(['laravel-messenger.drivers.spy-target' => ['enabled' => true]]);

        // Build the job directly targeting the spy driver
        $message = new Message('direct job test', 'spy-target');
        $job = new SendMessageJob($message);
        $job->handle(app(DispatchMessage::class));

        $this->assertTrue($tracker->called, 'The spy driver send() was not called by the job');
    }
}
