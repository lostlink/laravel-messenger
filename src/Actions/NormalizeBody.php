<?php

namespace Lostlink\Messenger\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class NormalizeBody
{
    public function __invoke(mixed $body): string
    {
        if ($body instanceof Jsonable) {
            return $body->toJson();
        }

        if ($body instanceof Arrayable) {
            return json_encode($body->toArray(), JSON_THROW_ON_ERROR);
        }

        if ($body instanceof \JsonSerializable) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        }

        if (is_array($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        }

        return (string) $body;
    }
}
