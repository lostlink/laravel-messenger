<?php

namespace Lostlink\Messenger\Macros\Collection;

use Closure;

class FromJson
{
    public function __invoke(): Closure
    {
        return fn (string $json) => collect(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
