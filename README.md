# LostLink Laravel-Messenger

`LostLink Laravel-Messenger` is a Laravel package for sending messages to various services asynchronously without blocking the processing of your application. Messages are queued and sent during the PHP `destruct()` phase, ensuring that the application’s performance is not impacted by external service communication.

## Features

- **Non-blocking message dispatch**: Messages are processed after the PHP request lifecycle.
- **Extensible driver system**: Add custom drivers to send messages to services of your choice.
- **Simple configuration**: Set up different drivers with environment variables.

### Supported Drivers

- [Amazon Kinesis](https://aws.amazon.com/kinesis/)
- [Tinybird](https://www.tinybird.co/)
- **Log**: Send messages to the Laravel log for debugging or local development.

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

    'default' => env('LARAVEL_MESSENGER_DRIVER', 'kinesis'),

    'drivers' => [

        'log' => [
            'class' => Lostlink\Messenger\Drivers\Log::class,
        ],

        'kinesis' => [
            'class' => Lostlink\Messenger\Drivers\Kinesis::class,
            'name' => env('LARAVEL_MESSENGER_KINESIS_STREAM_NAME'),
            'region' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION', env('AWS_DEFAULT_REGION')),
            'aws_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY', env('AWS_ACCESS_KEY_ID')),
            'aws_secret_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
        ],

        'tinybird' => [
            'class' => Lostlink\Messenger\Drivers\Tinybird::class,
            'name' => env('LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME'),
            'token' => env('LARAVEL_MESSENGER_TINYBIRD_TOKEN'),
            'endpoint' => env('LARAVEL_MESSENGER_TINYBIRD_ENDPOINT', 'https://api.us-east.aws.tinybird.co/v0/events'),
        ],

    ],

];
```

### Available Environment Variables

| Option | Description |
|--------|-------------|
| `LARAVEL_MESSENGER_DRIVER` | Specifies the driver to use (`log`, `kinesis`, or `tinybird`). |
| `LARAVEL_MESSENGER_KINESIS_STREAM_NAME` | The name of the Kinesis stream. |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION` | The AWS region where the Kinesis stream is located. Defaults to `AWS_DEFAULT_REGION` if not set. |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY` | The AWS access key for Kinesis. Defaults to `AWS_ACCESS_KEY_ID`. |
| `LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY` | The AWS secret key for Kinesis. Defaults to `AWS_SECRET_ACCESS_KEY`. |
| `LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME` | The name of the Tinybird data source. |
| `LARAVEL_MESSENGER_TINYBIRD_TOKEN` | The API token for Tinybird. |
| `LARAVEL_MESSENGER_TINYBIRD_ENDPOINT` | The Tinybird API endpoint. Defaults to `https://api.us-east.aws.tinybird.co/v0/events`. |

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

You can modify the configuration for a specific message by chaining the `config()` method. This allows you to override the default driver settings for that particular message. Here's an example:

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
]);
```

### Adding Custom Drivers

To create and add your own driver, simply extend the driver system by implementing the required interfaces. Follow the documentation for details on how to build custom drivers.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
