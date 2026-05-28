<?php

namespace Lostlink\Messenger\Contracts;

interface HasPersistentConnection
{
    public function close(): void;
}
