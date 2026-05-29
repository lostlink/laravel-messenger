<?php

namespace Lostlink\Messenger\Drivers;

use Aws\Kinesis\KinesisClient;
use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Exceptions\TransportException;
use Lostlink\Messenger\Message;

final class KinesisDriver implements Driver
{
    private ?KinesisClient $client = null;

    public function send(Message $message, array $config): void
    {
        $streamName = $message->attributes['stream'] ?? $config['name'];
        $partitionKey = $message->attributes['partitionKey'] ?? uniqid();

        // Client is cached for the lifetime of this driver instance. Per-message credential
        // overrides via configOverrides are not applied after the first send().
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

        try {
            $this->client->putRecord([
                'Data' => (new NormalizeBody)($message->body),
                'StreamName' => $streamName,
                'PartitionKey' => $partitionKey,
            ]);
        } catch (\Throwable $e) {
            throw new TransportException("Kinesis putRecord failed: {$e->getMessage()}", 0, $e);
        }
    }
}
