<?php

namespace Lostlink\Messenger\Drivers;

use Aws\Kinesis\KinesisClient;
use Lostlink\Messenger\PendingMessage;

class Kinesis extends Driver
{
    public function handle(): void
    {
        $kinesisConfig = collect([
            'region' => $this->message->config->get('region'),
            'version' => '2013-12-02',
        ]);

        if ($this->message->config->get('aws_key') && $this->message->config->get('aws_secret_key')) {
            $kinesisConfig = $kinesisConfig->merge([
                'credentials' => [
                    'key' => $this->message->config->get('aws_key'),
                    'secret' => $this->message->config->get('aws_secret_key'),
                ],
            ]);
        }

        $kinesisClient = new KinesisClient($kinesisConfig->toArray());

        $kinesisClient->PutRecord([
            'Data' => is_array($this->message->body) ? json_encode($this->message->body) : $this->message->body,
            'StreamName' => $this->message->stream ?? $this->message->config->get('name'),
            'PartitionKey' => $this->message->partitionKey ?? uniqid(),
        ]);
    }
}
