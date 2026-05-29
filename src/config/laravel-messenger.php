<?php

return [
    'default' => env('LARAVEL_MESSENGER_DRIVER', 'log'),

    'drivers' => [

        'log' => [
            'enabled' => env('LARAVEL_MESSENGER_LOG_ENABLED', true),
            'class' => Lostlink\Messenger\Drivers\LogDriver::class,
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_LOG_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_LOG_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'socket' => [
            'enabled' => env('LARAVEL_MESSENGER_SOCKET_ENABLED', false),
            'class' => Lostlink\Messenger\Drivers\SocketDriver::class,
            'host' => env('LARAVEL_MESSENGER_SOCKET_HOST'),
            'port' => env('LARAVEL_MESSENGER_SOCKET_PORT'),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_SOCKET_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_SOCKET_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'kinesis' => [
            'enabled' => env('LARAVEL_MESSENGER_KINESIS_ENABLED', false),
            'class' => Lostlink\Messenger\Drivers\KinesisDriver::class,
            'name' => env('LARAVEL_MESSENGER_KINESIS_STREAM_NAME'),
            'region' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION', env('AWS_DEFAULT_REGION')),
            'aws_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY', env('AWS_ACCESS_KEY_ID')),
            'aws_secret_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_KINESIS_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_KINESIS_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'tinybird' => [
            'enabled' => env('LARAVEL_MESSENGER_TINYBIRD_ENABLED', false),
            'class' => Lostlink\Messenger\Drivers\TinybirdDriver::class,
            'name' => env('LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME'),
            'token' => env('LARAVEL_MESSENGER_TINYBIRD_TOKEN'),
            'endpoint' => env('LARAVEL_MESSENGER_TINYBIRD_ENDPOINT', 'https://api.us-east.aws.tinybird.co/v0/events'),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_MAX_ATTEMPTS', 40), // Free tier limit
                'decay_seconds' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_DECAY_SECONDS', 3600), // Free tier limit
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_TINYBIRD_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_TINYBIRD_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'vector' => [
            'enabled' => env('LARAVEL_MESSENGER_VECTOR_ENABLED', false),
            'class' => \Lostlink\Messenger\Drivers\VectorDriver::class,
            'protocol' => env('LARAVEL_MESSENGER_VECTOR_PROTOCOL', 'tcp'),
            'host' => env('LARAVEL_MESSENGER_VECTOR_HOST'),
            'port' => env('LARAVEL_MESSENGER_VECTOR_PORT'),
            'timeout' => (int) env('LARAVEL_MESSENGER_VECTOR_TIMEOUT', 2),
            'persistent' => env('LARAVEL_MESSENGER_VECTOR_PERSISTENT', false),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_ENABLED', false),
                'max_attempts' => (int) env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => (int) env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_VECTOR_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_VECTOR_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'queue' => [
            'enabled' => env('LARAVEL_MESSENGER_QUEUE_ENABLED', false),
            'class' => \Lostlink\Messenger\Drivers\QueueDriver::class,
            'driver' => env('LARAVEL_MESSENGER_QUEUE_TARGET_DRIVER', 'log'),
            'connection' => env('LARAVEL_MESSENGER_QUEUE_CONNECTION'),
            'queue' => env('LARAVEL_MESSENGER_QUEUE_NAME'),
            'delay' => (int) env('LARAVEL_MESSENGER_QUEUE_DELAY', 0),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_ENABLED', false),
                'max_attempts' => (int) env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => (int) env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid' => env('LARAVEL_MESSENGER_QUEUE_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_QUEUE_ENVELOPE_TIMESTAMP', false),
            ],
        ],

    ],
];
