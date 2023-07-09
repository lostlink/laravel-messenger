<?php

return [
    'default' => env('LARAVEL_MESSENGER_DRIVER', 'kinesis'),

    'drivers' => [

        'kinesis' => [
            'class' => Lostlink\Messenger\Drivers\Kinesis::class,
            'name' => env('LARAVEL_MESSENGER_KINESIS_STREAM_NAME'),
            'region' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION', env('AWS_DEFAULT_REGION')),
            'aws_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY', env('AWS_ACCESS_KEY_ID')),
            'aws_secret_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
        ],

    ],
];
