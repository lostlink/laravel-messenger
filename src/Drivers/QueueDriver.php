<?php

namespace Lostlink\Messenger\Drivers;

use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Exceptions\ConfigMalFormedException;
use Lostlink\Messenger\Jobs\SendMessageJob;
use Lostlink\Messenger\Message;

final class QueueDriver implements Driver
{
    public function send(Message $message, array $config): void
    {
        if (empty($config['driver'])) {
            throw new ConfigMalFormedException("Queue driver requires a 'driver' config key specifying the target driver");
        }

        $targetMessage = new Message($message->body, $config['driver'], $message->attributes);

        $job = SendMessageJob::dispatch($targetMessage);

        if (!empty($config['connection'])) {
            $job = $job->onConnection($config['connection']);
        }

        if (!empty($config['queue'])) {
            $job = $job->onQueue($config['queue']);
        }

        if (!empty($config['delay'])) {
            $job->delay($config['delay']);
        }
    }
}
