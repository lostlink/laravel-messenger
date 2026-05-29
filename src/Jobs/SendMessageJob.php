<?php

namespace Lostlink\Messenger\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lostlink\Messenger\Actions\DispatchMessage;
use Lostlink\Messenger\Message;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Message $message) {}

    public function handle(DispatchMessage $dispatcher): void
    {
        $dispatcher($this->message);
    }
}
