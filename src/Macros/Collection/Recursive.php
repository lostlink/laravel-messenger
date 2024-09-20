<?php

namespace Lostlink\Messenger\Macros\Collection;

use Closure;

class Recursive
{
    public function __invoke(): Closure
    {
        return fn () => $this->map(static function ($value) {
            if (is_array($value) || is_object($value)) {
                return collect($value)->recursive();
            }

            return $value;
        });
    }
}
