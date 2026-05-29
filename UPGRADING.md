# Upgrading Guide

## v1 to v2

### Driver class renames

All built-in driver classes have been given a `Driver` suffix to avoid collisions with PHP reserved words and to be explicit about their role.

| v1 class | v2 class |
|----------|----------|
| `Lostlink\Messenger\Drivers\Log` | `Lostlink\Messenger\Drivers\LogDriver` |
| `Lostlink\Messenger\Drivers\Socket` | `Lostlink\Messenger\Drivers\SocketDriver` |
| `Lostlink\Messenger\Drivers\Kinesis` | `Lostlink\Messenger\Drivers\KinesisDriver` |
| `Lostlink\Messenger\Drivers\Tinybird` | `Lostlink\Messenger\Drivers\TinybirdDriver` |

Update the `class` key in your published `config/laravel-messenger.php` for each driver you use.

---

### Driver contract

The abstract `Driver` base class has been removed. Drivers no longer extend a base class; they implement a pure interface.

**v1**:
```php
use Lostlink\Messenger\Drivers\Driver;

class MyDriver extends Driver
{
    public function handle(): void
    {
        // $this->message->body
        // $this->message->config->get('token')
    }
}
```

**v2**:
```php
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Message;

class MyDriver implements Driver
{
    public function send(Message $message, array $config): void
    {
        // $message->body
        // $config['token']
    }
}
```

The `DriverContract` alias is also removed. Import `Lostlink\Messenger\Contracts\Driver` directly.

Key differences:

- The method is `send(Message $message, array $config): void`, not `handle(): void`.
- The message body is on `$message->body`.
- The resolved driver config (merged with per-message overrides) is passed as `$config` — no need to call `config()` yourself.
- Throw any `\Throwable` to signal failure. Do not catch and swallow exceptions inside drivers; the `DispatchMessage` action handles that.

---

### Magic `__call` removed

v1 allowed setting driver-specific attributes via magic methods on the fluent builder (e.g. `->stream('name')`, `->partitionKey('key')`). These are now explicit methods on `PendingMessage`.

**v1**:
```php
Messenger::send($payload)->stream('my-stream')->partitionKey('key');
```

**v2** — identical syntax, but the methods are now declared explicitly:
```php
Messenger::send($payload)->stream('my-stream')->partitionKey('key');
```

If you called any other magic methods beyond `stream`, `partitionKey`, `token`, and `endpoint` they no longer exist. Move that data into the `config([])` override instead.

---

### Rate limiting and error handling moved out of drivers

In v1, drivers were expected to implement their own rate limiting and try/catch blocks. In v2 both concerns are handled entirely by the `DispatchMessage` action:

- Rate limiting is applied before `send()` is called.
- Exceptions thrown by `send()` are caught by `DispatchMessage`, which fires a `MessageFailed` event and returns a `DispatchResult`.

Remove any `RateLimiter` usage and top-level try/catch blocks from custom drivers.

---

### `DispatchResult`

Dispatch no longer returns `void` or a boolean. The `DispatchMessage` action now returns a `Lostlink\Messenger\Results\DispatchResult`:

```php
$result = app(\Lostlink\Messenger\Actions\DispatchMessage::class)($message);

$result->status;        // bool — true on success
$result->errorMessage;  // ?string — exception message or failure reason
$result->driver;        // string — driver name
$result->attempts;      // int — 1 on success, 0 otherwise
```

`PendingMessage::__destruct()` calls `DispatchMessage` internally and discards the result. If you need the result, call `DispatchMessage` directly rather than relying on `__destruct`.

---

### Per-driver `enabled` flag

Each driver config now has an `enabled` key (default `true` for `log`, `false` for all others). When `enabled` is `false`, dispatch is skipped and a `DispatchResult` with `status = false` is returned immediately — no exception is thrown.

Add the key to any custom driver entries you already have in config:

```php
'my-driver' => [
    'enabled' => env('MY_DRIVER_ENABLED', true),
    // ...
],
```

---

### New `envelope` config block

Every driver now supports an optional `envelope` block:

```php
'envelope' => [
    'uuid'      => false,  // prepend '_id' (UUID v4)
    'timestamp' => false,  // prepend '_sent_at' (ISO-8601)
],
```

Both default to `false`. No action is required unless you want to enable them.

---

### New drivers

Two new drivers are available out of the box:

- **`vector`** — sends newline-delimited JSON to a [Vector](https://vector.dev/) agent over TCP or UDP. See the README for the full config reference.
- **`queue`** — dispatches the message as a Laravel queued job targeting another Messenger driver. Useful for deferring slow network calls to a worker.

Both are disabled by default (`enabled = false`). Enable them in your config or via environment variables.

---

### Config file changes summary

If you have a published `config/laravel-messenger.php`, apply these changes:

1. Update `class` values to use the `*Driver` suffix for all built-in drivers.
2. Add `'enabled' => env('...', true|false)` to each driver entry.
3. Add an `'envelope' => ['uuid' => false, 'timestamp' => false]` block to each driver entry.
4. Optionally add the `vector` and `queue` driver entries.
