<?php

namespace Lostlink\Messenger\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lostlink\Messenger\Message;

class ApplyEnvelope
{
    public function __invoke(Message $message, array $config): Message
    {
        $attributes = $message->attributes;

        if (Arr::get($config, 'envelope.uuid') === true && ! array_key_exists('_id', $attributes)) {
            $attributes['_id'] = Str::uuid()->toString();
        }

        if (Arr::get($config, 'envelope.timestamp') === true && ! array_key_exists('_sent_at', $attributes)) {
            $attributes['_sent_at'] = now()->toIso8601String();
        }

        return new Message($message->body, $message->driver, $attributes);
    }
}
