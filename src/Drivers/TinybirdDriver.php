<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Support\Facades\Http;
use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Message;

final class TinybirdDriver implements Driver
{
    public function send(Message $message, array $config): void
    {
        $token = $message->attributes['token'] ?? $config['token'];
        $endpoint = $message->attributes['endpoint'] ?? $config['endpoint'];
        $name = $message->attributes['stream'] ?? $config['name'];

        $normalized = (new NormalizeBody)($message->body);

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withQueryParameters(['name' => $name])
            ->send('POST', $endpoint, ['body' => $normalized]);

        $response->throw();
    }
}
