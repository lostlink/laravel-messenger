<?php

namespace Lostlink\Messenger\Results;

final class DispatchResult
{
    public bool $status;

    public ?string $errorMessage;

    public string $driver;

    public int $attempts;

    public function __construct(bool $status, ?string $errorMessage, string $driver, int $attempts)
    {
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->driver = $driver;
        $this->attempts = $attempts;
    }
}
