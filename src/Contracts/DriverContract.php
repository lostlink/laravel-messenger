<?php

namespace Lostlink\Messenger\Contracts;

use Lostlink\Messenger\PendingMessage;

interface DriverContract
{
    public function send(PendingMessage $message): DriverContract;

    public function handle(): void;
}
