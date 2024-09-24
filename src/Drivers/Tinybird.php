<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lostlink\Messenger\Exceptions\BodyMalFormedException;

class Tinybird extends Driver
{
    /**
     * @throws RequestException
     * @throws BodyMalFormedException
     * @throws ConnectionException
     */
    public function handle(): void
    {
        if (! is_array($this->message->body)) {
            throw (new BodyMalFormedException('Body must be an array'));
        }

        $response = Http::withToken($this->message->token ?? $this->message->config->get('token'))
            ->acceptJson()
            ->withQueryParameters([
                'name' => $this->message->stream ?? $this->message->config->get('name'),
            ])
            ->post(
                $this->message->endpoint ?? $this->message->config->get('endpoint'),
                $this->message->body
            );

        $response->throw();
    }
}
