<?php

namespace Lostlink\Messenger\Drivers;

use Aws\Exception\AwsException;
use Aws\Kinesis\KinesisClient;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\PendingMessage;

class Kinesis implements Driver
{
    public ?string $message = null;

    public bool $status = false;

    public function send(PendingMessage $message): Driver
    {
        $kinesisConfig = collect([
            'region' => $message->config->get('region'),
            'version' => '2013-12-02',
        ]);

        if ($message->config->get('aws_key') && $message->config->get('aws_secret_key')) {
            $kinesisConfig = $kinesisConfig->merge([
                'credentials' => [
                    'key' => $message->config->get('aws_key'),
                    'secret' => $message->config->get('aws_secret_key'),
                ],
            ]);
        }

        $kinesisClient = new KinesisClient($kinesisConfig->toArray());

        try {
            $kinesisClient->PutRecord([
                'Data' => is_array($message->body) ? json_encode($message->body) : $message->body,
                'StreamName' => $message->stream ?? $message->config->get('name'),
                'PartitionKey' => $message->partitionKey ?? uniqid(),
            ]);
            $this->status = true;
        } catch (AwsException $e) {
            $this->status = false;
            $this->message = $e->getMessage();
        }

        return $this;
    }
}
