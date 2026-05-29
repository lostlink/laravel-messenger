<?php

namespace Lostlink\Messenger\Drivers;

use Aws\Kinesis\KinesisClient;
use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Message;

final class KinesisDriver implements Driver
{
    private ?KinesisClient $client = null;

    public function send(Message $message, array $config): void
    {
        $streamName = $message->attributes['stream'] ?? $config['name'];
        $partitionKey = $message->attributes['partitionKey'] ?? uniqid();

        if ($this->client === null) {
            $clientConfig = [
                'region' => $config['region'],
                'version' => '2013-12-02',
            ];

            if (! empty($config['aws_key']) && ! empty($config['aws_secret_key'])) {
                $clientConfig['credentials'] = [
                    'key' => $config['aws_key'],
                    'secret' => $config['aws_secret_key'],
                ];
            }

            $this->client = new KinesisClient($clientConfig);
        }

        $this->client->putRecord([
            'Data' => (new NormalizeBody)($message->body),
            'StreamName' => $streamName,
            'PartitionKey' => $partitionKey,
        ]);
    }
}
