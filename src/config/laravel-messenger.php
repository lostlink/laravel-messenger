<?php

return [
    'default' => env('LARAVEL_MESSENGER_DRIVER', 'log'),

    'drivers' => [

        'log' => [
            'enabled' => env('LARAVEL_MESSENGER_LOG_ENABLED', true),
            'class' => Lostlink\Messenger\Drivers\Log::class,
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
        ],

        'kinesis' => [
            'enabled' => env('LARAVEL_MESSENGER_KINESIS_ENABLED', false),
            'class' => Lostlink\Messenger\Drivers\Kinesis::class,
            'name' => env('LARAVEL_MESSENGER_KINESIS_STREAM_NAME'),
            'region' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION', env('AWS_DEFAULT_REGION')),
            'aws_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY', env('AWS_ACCESS_KEY_ID')),
            'aws_secret_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
        ],

        'tinybird' => [
            'enabled' => env('LARAVEL_MESSENGER_TINYBIRD_ENABLED', false),
            'class' => Lostlink\Messenger\Drivers\Tinybird::class,
            'name' => env('LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME'),
            'token' => env('LARAVEL_MESSENGER_TINYBIRD_TOKEN'),
            'endpoint' => env('LARAVEL_MESSENGER_TINYBIRD_ENDPOINT', 'https://api.us-east.aws.tinybird.co/v0/events'),
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_MAX_ATTEMPTS', 40), // Free tier limit
                'decay_seconds' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_DECAY_SECONDS', 3600), // Free tier limit
            ],
        ],

    ],
];
