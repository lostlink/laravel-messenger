# LostLink Laravel-Messenger

`LostLink Laravel-Messenger` is a Laravel package for sending messages to various services asynchronously without blocking the processing of your application. Messages are queued and sent during the PHP `destruct()` phase, ensuring that the application’s performance is not impacted by external service communication.

## Features

- **Non-blocking message dispatch**: Messages are processed after the PHP request lifecycle.
- **Extensible driver system**: Add custom drivers to send messages to services of your choice.
- **Simple configuration**: Set up different drivers with environment variables.
- **Rate limiting**: Control the rate at which messages are sent to avoid exceeding service limits.

### Supported Drivers

- [Log](https://laravel.com/docs/logging/)
- [Amazon Kinesis](https://aws.amazon.com/kinesis/)
- [Tinybird](https://www.tinybird.co/)

## Installation

1. Install the package via Composer:

   ```bash
   composer require lostlink/laravel-messenger
   ```

2. Publish the configuration file (optional):

   ```bash
   php artisan vendor:publish --tag=messenger-config
   ```

3. Configure the environment variables for the drivers you wish to use.

## Configuration

The package supports multiple drivers, and you can set the default driver using environment variables. Here’s an example configuration file:

```php
return [

    'default' => env('LARAVEL_MESSENGER_DRIVER', 'log'),

    'drivers' => [

        'log' => [
            'class' => Lostlink\Messenger\Drivers\Log::class,
            'rate_limit' => [
                'enabled' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_ENABLED', false),
                'max_attempts' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
        ],

        'kinesis' => [
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
```

### Available Environment Variables

| Option                                                | Description                                                                                                                        |
|-------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------|
| `LARAVEL_MESSENGER_DRIVER`                            | Specifies the driver to use (`log`, `kinesis`, or `tinybird`).                                                                     |
| `LARAVEL_MESSENGER_LOG_RATE_LIMIT_ENABLED`            | Enable rate limiting for the log driver.                                                                                           |
| `LARAVEL_MESSENGER_LOG_RATE_LIMIT_MAX_ATTEMPTS`       | The maximum number of attempts before rate limiting is enforced for the Log driver. A value of 0 is the same a being disabled      |
| `LARAVEL_MESSENGER_LOG_RATE_LIMIT_DECAY_SECONDS`      | The number of seconds before the rate limit resets for the log driver.                                                             |
| `LARAVEL_MESSENGER_KINESIS_STREAM_NAME`               | The name of the Kinesis stream.                                                                                                    |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION`         | The AWS region where the Kinesis stream is located. Defaults to `AWS_DEFAULT_REGION` if not set.                                   |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY`            | The AWS access key for Kinesis. Defaults to `AWS_ACCESS_KEY_ID`.                                                                   |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY`     | The AWS secret key for Kinesis. Defaults to `AWS_SECRET_ACCESS_KEY`.                                                               |
| `LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_ENABLED`        | Enable rate limiting for the Kinesis driver.                                                                                       |
| `LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_MAX_ATTEMPTS`   | The maximum number of attempts before rate limiting is enforced for the Kinesis driver. A value of 0 is the same a being disabled  |
| `LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_DECAY_SECONDS`  | The number of seconds before the rate limit resets for the Kinesis driver.                                                         |
| `LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME`         | The name of the Tinybird data source.                                                                                              |
| `LARAVEL_MESSENGER_TINYBIRD_TOKEN`                    | The API token for Tinybird.                                                                                                        |
| `LARAVEL_MESSENGER_TINYBIRD_ENDPOINT`                 | The Tinybird API endpoint. Defaults to `https://api.us-east.aws.tinybird.co/v0/events`.                                            |
| `LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_ENABLED`       | Enable rate limiting for the Tinybird driver.                                                                                      |
| `LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_MAX_ATTEMPTS`  | The maximum number of attempts before rate limiting is enforced for the Tinybird driver. A value of 0 is the same a being disabled |
| `LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_DECAY_SECONDS` | The number of seconds before the rate limit resets for the Tinybird driver.                                                        |

## Usage

Sending messages with `LostLink Laravel-Messenger` is simple. After configuring your environment variables, you can use the `send` method:

```php
use \Lostlink\Messenger\Messenger;

Messenger::send([
   "timestamp" => "2022-10-27T11:43:02.099Z", 
   "transaction_id" => "8d1e1533-6071-4b10-9cda-b8429c1c7a67", 
   "name" => "Bobby Drake", 
   "email" => "bobby.drake@pressure.io", 
   "age" => 42, 
   "passport_number" => 3847665, 
   "flight_from" => "Barcelona", 
   "flight_to" => "London", 
   "extra_bags" => 1, 
   "flight_class" => "economy", 
   "priority_boarding" => false, 
   "meal_choice" => "vegetarian", 
   "seat_number" => "15D", 
   "airline" => "Red Balloon" 
]);
```

### Updating Configuration on the Fly

You can modify the configuration for a specific message by chaining and passing an array to the `config([])` method. This allows you to override the default driver settings for that particular message. Here's an example:

```php
use \Lostlink\Messenger\Messenger;

Messenger::send([
   "timestamp" => "2022-10-27T11:43:02.099Z", 
   "transaction_id" => "8d1e1533-6071-4b10-9cda-b8429c1c7a67", 
   "name" => "Bobby Drake", 
   "email" => "bobby.drake@pressure.io", 
   "age" => 42, 
   "passport_number" => 3847665, 
   "flight_from" => "Barcelona", 
   "flight_to" => "London", 
   "extra_bags" => 1, 
   "flight_class" => "economy", 
   "priority_boarding" => false, 
   "meal_choice" => "vegetarian", 
   "seat_number" => "15D", 
   "airline" => "Red Balloon" 
])->config([
    'driver' => 'tinybird', // Change the driver to "tinybird" for this message
    'name' => 'example_events', // Change the data source name to "example_events" for this message
    'token' => 'your_token', // Change the token for this message
    'endpoint' => 'https://api.us-east.aws.tinybird.co/v0/events', // Change the endpoint for this message
    'rate_limit' => [
        'enabled' => true, // Enable rate limiting for this message to use Tinybird Free Tier @ 1000/day
        'max_attempts' => 40, // Set the maximum number of attempts before rate limiting is enforced
        'decay_seconds' => 3600, // Set the number of seconds before the rate limit resets
    ],
]);
```

### Adding Custom Drivers

To create and add your own driver, simply extend the driver system by implementing the required interfaces.

1. Make sure to publish the laravel-messenger config file
   ```bash
   php artisan vendor:publish --tag=messenger-config
   ```

2. Adjust the laravel-messenger config with the name and class of your custom driver
   ```php
   
   return [
        ...   

       'drivers' => [
   
            ...

           'mycustomdriver' => [
               'class' => \App\Messenger\Drivers\MyCustomDriver::class,
               'name' => env('MY_CUSTOM_DRIVER_NAME'),
               'token' => env('MY_CUSTOM_DRIVER_TOKEN'),
               'endpoint' => env('MY_CUSTOM_DRIVER_ENDPOINT'),
               'rate_limit' => [
                   'enabled' => env('MY_CUSTOM_DRIVER_RATE_LIMIT_ENABLED', false),
                   'max_attempts' => env('MY_CUSTOM_DRIVER_RATE_LIMIT_MAX_ATTEMPTS', 10),
                   'decay_seconds' => env('MY_CUSTOM_DRIVER_RATE_LIMIT_DECAY_SECONDS', 60),
               ],
           ],
   
       ],
   
       ...

   ];
   ```


3. Create a class that extends \LostLink\Drivers\Driver
   ````php
   class MyCustomDriver extends Driver
   {
       public function handle(): void
       {
           $response = Http::withToken($this->message->token ?? $this->message->config->get('token'))
               ->acceptJson()
               ->withQueryParameters([
                   'name' => $this->message->stream ?? $this->message->config->get('name'),
               ])
               ->post(
                   $this->message->endpoint ?? $this->message->config->get('endpoint'),
                   $this->message->body
               );
   
           $response->throw();
       }
   }
   ```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
