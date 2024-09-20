<?php

namespace Lostlink\Messenger\Drivers;

use Illuminate\Support\Facades\Http;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Exceptions\BodyMalFormedException;
use Lostlink\Messenger\PendingMessage;

class Tinybird implements Driver
{
    public ?string $message = null;

    public bool $status = false;

    public function send(PendingMessage $message): Driver
    {
        try {
            if (! is_array($message->body)) {
                throw (new BodyMalFormedException('Body must be an array'));
            }

            $response = Http::withToken($message->token ?? $message->config->get('token'))
                ->acceptJson()
                ->withQueryParameters([
                    'name' => $message->stream ?? $message->config->get('name'),
                ])
                ->post(
                    $message->endpoint ?? $message->config->get('endpoint'),
                    $message->body
                );

            $response->throw();

            $this->status = true;

        } catch (\Exception $e) {

            $this->status = false;

            $this->message = $e->getMessage();

        }

        return $this;
    }
}
