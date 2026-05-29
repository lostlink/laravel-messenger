# LostLink Laravel-Messenger

`LostLink Laravel-Messenger` is a Laravel package for sending messages to various services without blocking the application request lifecycle. Messages are dispatched during PHP's `__destruct()` phase, after the response has been sent.

## Features

- **Non-blocking dispatch**: Messages are processed after the PHP request lifecycle ends.
- **Extensible driver system**: Register custom drivers with a single closure.
- **Per-driver rate limiting**: Protect external services from being overwhelmed.
- **Auto-envelope**: Optionally stamp each message with a UUID and ISO-8601 timestamp.
- **Events**: Hook into `MessageSending`, `MessageSent`, and `MessageFailed`.

### Built-in Drivers

| Driver | Destination |
|--------|-------------|
| `log` | Laravel's logger |
| `socket` | Raw TCP socket via `fsockopen` |
| `kinesis` | [Amazon Kinesis Data Streams](https://aws.amazon.com/kinesis/) |
| `tinybird` | [Tinybird Events API](https://www.tinybird.co/) |
| `vector` | [Vector](https://vector.dev/) over TCP or UDP |
| `queue` | Laravel queue (dispatches to another Messenger driver) |

---

## Installation

```bash
composer require lostlink/laravel-messenger
```

The service provider is auto-discovered. No manual registration is required.

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag=messenger-config
```

---

## Quick Start

```php
use Lostlink\Messenger\Messenger;

// Send using the default driver (configured via LARAVEL_MESSENGER_DRIVER)
Messenger::send(['event' => 'user.registered', 'user_id' => 42]);

// Send using a specific driver
Messenger::send(['event' => 'user.registered', 'user_id' => 42])
    ->driver('log');
```

Dispatch happens automatically when the `PendingMessage` object goes out of scope (`__destruct`). You do not need to call any termination method.

---

## Builder Methods

All methods return the `PendingMessage` instance for chaining.

### `driver(string $driver)`

Overrides the default driver for this message.

```php
Messenger::send($payload)->driver('tinybird');
```

### `config(array $config)`

Merges additional config on top of the driver's global config for this message only.

```php
Messenger::send($payload)
    ->driver('tinybird')
    ->config([
        'name'  => 'my_datasource',
        'token' => 'tok_live_xxxx',
    ]);
```

### `stream(string $value)`

Sets a stream/data-source name for drivers that route by name (e.g. Kinesis, Tinybird).

```php
Messenger::send($payload)->driver('kinesis')->stream('my-stream');
```

### `partitionKey(string $value)`

Sets the Kinesis partition key (or equivalent routing key for other drivers).

```php
Messenger::send($payload)->driver('kinesis')->partitionKey('tenant-123');
```

### `token(string $value)`

Overrides the auth token for this message.

```php
Messenger::send($payload)->driver('tinybird')->token('tok_live_xxxx');
```

### `endpoint(string $value)`

Overrides the target endpoint URL for this message.

```php
Messenger::send($payload)->driver('tinybird')->endpoint('https://api.eu-central.aws.tinybird.co/v0/events');
```

---

## Configuration

```php
// config/laravel-messenger.php
return [
    'default' => env('LARAVEL_MESSENGER_DRIVER', 'log'),

    'drivers' => [

        'log' => [
            'enabled' => env('LARAVEL_MESSENGER_LOG_ENABLED', true),
            'class'   => Lostlink\Messenger\Drivers\LogDriver::class,
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_LOG_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_LOG_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_LOG_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'socket' => [
            'enabled' => env('LARAVEL_MESSENGER_SOCKET_ENABLED', false),
            'class'   => Lostlink\Messenger\Drivers\SocketDriver::class,
            'host'    => env('LARAVEL_MESSENGER_SOCKET_HOST'),
            'port'    => env('LARAVEL_MESSENGER_SOCKET_PORT'),
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_SOCKET_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_SOCKET_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_SOCKET_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'kinesis' => [
            'enabled'        => env('LARAVEL_MESSENGER_KINESIS_ENABLED', false),
            'class'          => Lostlink\Messenger\Drivers\KinesisDriver::class,
            'name'           => env('LARAVEL_MESSENGER_KINESIS_STREAM_NAME'),
            'region'         => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_REGION', env('AWS_DEFAULT_REGION')),
            'aws_key'        => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_KEY', env('AWS_ACCESS_KEY_ID')),
            'aws_secret_key' => env('LARAVEL_MESSENGER_KINESIS_STREAM_AWS_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_KINESIS_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_KINESIS_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_KINESIS_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'tinybird' => [
            'enabled'  => env('LARAVEL_MESSENGER_TINYBIRD_ENABLED', false),
            'class'    => Lostlink\Messenger\Drivers\TinybirdDriver::class,
            'name'     => env('LARAVEL_MESSENGER_TINYBIRD_DATA_SOURCE_NAME'),
            'token'    => env('LARAVEL_MESSENGER_TINYBIRD_TOKEN'),
            'endpoint' => env('LARAVEL_MESSENGER_TINYBIRD_ENDPOINT', 'https://api.us-east.aws.tinybird.co/v0/events'),
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_MAX_ATTEMPTS', 40),
                'decay_seconds' => env('LARAVEL_MESSENGER_TINYBIRD_RATE_LIMIT_DECAY_SECONDS', 3600),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_TINYBIRD_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_TINYBIRD_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'vector' => [
            'enabled'    => env('LARAVEL_MESSENGER_VECTOR_ENABLED', false),
            'class'      => Lostlink\Messenger\Drivers\VectorDriver::class,
            'protocol'   => env('LARAVEL_MESSENGER_VECTOR_PROTOCOL', 'tcp'),
            'host'       => env('LARAVEL_MESSENGER_VECTOR_HOST'),
            'port'       => env('LARAVEL_MESSENGER_VECTOR_PORT'),
            'timeout'    => env('LARAVEL_MESSENGER_VECTOR_TIMEOUT', 2),
            'persistent' => env('LARAVEL_MESSENGER_VECTOR_PERSISTENT', false),
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_VECTOR_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_VECTOR_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_VECTOR_ENVELOPE_TIMESTAMP', false),
            ],
        ],

        'queue' => [
            'enabled'    => env('LARAVEL_MESSENGER_QUEUE_ENABLED', false),
            'class'      => Lostlink\Messenger\Drivers\QueueDriver::class,
            'driver'     => env('LARAVEL_MESSENGER_QUEUE_TARGET_DRIVER', 'log'),
            'connection' => env('LARAVEL_MESSENGER_QUEUE_CONNECTION'),
            'queue'      => env('LARAVEL_MESSENGER_QUEUE_NAME'),
            'delay'      => env('LARAVEL_MESSENGER_QUEUE_DELAY', 0),
            'rate_limit' => [
                'enabled'       => env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_ENABLED', false),
                'max_attempts'  => env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_MAX_ATTEMPTS', 10),
                'decay_seconds' => env('LARAVEL_MESSENGER_QUEUE_RATE_LIMIT_DECAY_SECONDS', 60),
            ],
            'envelope' => [
                'uuid'      => env('LARAVEL_MESSENGER_QUEUE_ENVELOPE_UUID', false),
                'timestamp' => env('LARAVEL_MESSENGER_QUEUE_ENVELOPE_TIMESTAMP', false),
            ],
        ],

    ],
];
```

---

## Driver Reference

### log

Writes messages to Laravel's logger. Useful for local development and testing.

**Required config keys**: none

---

### socket

Sends a newline-delimited JSON payload over a raw TCP connection using `fsockopen`.

| Key | Default | Description |
|-----|---------|-------------|
| `host` | — | Hostname or IP |
| `port` | — | Port number |

---

### kinesis

Puts records onto an Amazon Kinesis Data Stream.

| Key | Default | Description |
|-----|---------|-------------|
| `name` | — | Stream name |
| `region` | `AWS_DEFAULT_REGION` | AWS region |
| `aws_key` | `AWS_ACCESS_KEY_ID` | AWS access key |
| `aws_secret_key` | `AWS_SECRET_ACCESS_KEY` | AWS secret key |

---

### tinybird

Posts events to the [Tinybird Events API](https://www.tinybird.co/docs/get-data-in/ingest-apis/events-api).

| Key | Default | Description |
|-----|---------|-------------|
| `name` | — | Data source name |
| `token` | — | API token |
| `endpoint` | `https://api.us-east.aws.tinybird.co/v0/events` | API endpoint |

The default `max_attempts` of 40 and `decay_seconds` of 3600 reflect the Tinybird free-tier limit.

---

### vector

Sends newline-delimited JSON to a [Vector](https://vector.dev/) agent over TCP or UDP.

| Key | Default | Description |
|-----|---------|-------------|
| `protocol` | `tcp` | Transport: `tcp` or `udp` |
| `host` | — | Hostname or IP |
| `port` | — | Port number |
| `timeout` | `2` | Connection/write timeout in seconds |
| `persistent` | `false` | Keep TCP socket open across messages in the same process |

**UDP** messages are limited to ~65 KB per datagram. Larger payloads raise a `TransportException`; switch to `tcp` instead.

**Persistent TCP** (`persistent = true`) reuses the socket within a single PHP process. The connection is closed when the `VectorDriver` instance is garbage-collected (implements `HasPersistentConnection`).

---

### queue

Dispatches the message as a Laravel queued job. The job re-sends the message through a separate Messenger driver, letting you defer expensive network calls to a worker.

| Key | Default | Description |
|-----|---------|-------------|
| `driver` | `log` | Target Messenger driver to use inside the job |
| `connection` | `null` | Queue connection name (uses the default if omitted) |
| `queue` | `null` | Queue name (uses the default if omitted) |
| `delay` | `0` | Delay in seconds before the job runs |

---

## Rate Limiting

Every driver supports rate limiting through the `rate_limit` config block:

```php
'rate_limit' => [
    'enabled'       => true,
    'max_attempts'  => 40,    // messages per window; 0 means no limit
    'decay_seconds' => 3600,  // window length in seconds
],
```

Rate limiting is enforced by `DispatchMessage` using Laravel's `RateLimiter`. When the limit is exceeded the message is silently dropped and a `DispatchResult` with `status = false` and `errorMessage = 'Rate limit exceeded'` is returned. Set `enabled = false` or `max_attempts = 0` to disable.

---

## Auto-Envelope

Each driver has an `envelope` block that adds metadata to every outgoing message before dispatch:

```php
'envelope' => [
    'uuid'      => true,  // adds '_id' (UUID v4 string)
    'timestamp' => true,  // adds '_sent_at' (ISO-8601 string)
],
```

Both default to `false`. The fields are added only when they are not already present in the message attributes.

---

## Events

| Event | Fired | Properties |
|-------|-------|------------|
| `Lostlink\Messenger\Events\MessageSending` | Before the driver's `send()` is called | `$message` |
| `Lostlink\Messenger\Events\MessageSent` | After a successful `send()` | `$message`, `$result` |
| `Lostlink\Messenger\Events\MessageFailed` | After an exception from `send()` | `$message`, `$result` |

`$result` is a `Lostlink\Messenger\Results\DispatchResult` with four properties:

| Property | Type | Description |
|----------|------|-------------|
| `status` | `bool` | `true` on success, `false` on failure or rate limit |
| `errorMessage` | `?string` | Exception message or reason for failure |
| `driver` | `string` | Driver name used |
| `attempts` | `int` | `1` on success, `0` otherwise |

---

## Custom Drivers

### Using `Messenger::extend()`

Register a driver with a factory closure. The closure receives the service container and must return an object implementing `Lostlink\Messenger\Contracts\Driver`.

```php
use Lostlink\Messenger\Messenger;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Contracts\Driver;

Messenger::extend('my-driver', function ($app) {
    return new class implements Driver {
        public function send(Message $message, array $config): void
        {
            // deliver $message->body using $config
        }
    };
});
```

Call `Messenger::extend()` in a service provider's `boot()` method.

### Via config

Alternatively, point the `class` key in `laravel-messenger.php` to your driver class:

```php
'my-driver' => [
    'enabled' => true,
    'class'   => \App\Messenger\Drivers\MyDriver::class,
    'api_key' => env('MY_DRIVER_API_KEY'),
    'rate_limit' => [
        'enabled'       => false,
        'max_attempts'  => 10,
        'decay_seconds' => 60,
    ],
    'envelope' => [
        'uuid'      => false,
        'timestamp' => false,
    ],
],
```

### The Driver contract

```php
namespace Lostlink\Messenger\Contracts;

interface Driver
{
    public function send(Message $message, array $config): void;
}
```

- `$message->body` is the raw payload (array or string).
- `$config` is the merged driver config (global config + per-message overrides).
- Throw any `\Throwable` to signal failure. The `DispatchMessage` action catches it and fires `MessageFailed`.

### Optional interfaces

**`HasPersistentConnection`** — implement this when your driver holds an open connection that should be explicitly closed:

```php
interface HasPersistentConnection
{
    public function close(): void;
}
```

**`SupportsBatch`** — implement this to handle bulk dispatch in a single call:

```php
interface SupportsBatch
{
    /** @param Message[] $messages */
    public function sendMany(array $messages, array $config): void;
}
```

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
