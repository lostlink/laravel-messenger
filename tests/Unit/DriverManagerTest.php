<?php

namespace Lostlink\Messenger\Tests\Unit;

use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Contracts\HasPersistentConnection;
use Lostlink\Messenger\DriverManager;
use Lostlink\Messenger\Drivers\LogDriver;
use Lostlink\Messenger\Exceptions\ConfigMalFormedException;
use Lostlink\Messenger\Exceptions\ConfigNotFoundException;
use Lostlink\Messenger\Exceptions\DriverClassNotFoundException;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Tests\TestCase;

class DriverManagerTest extends TestCase
{
    private DriverManager $manager;

    public function setUp(): void
    {
        parent::setUp();

        config(['laravel-messenger.drivers.testlog' => [
            'class'   => LogDriver::class,
            'enabled' => true,
        ]]);

        $this->manager = new DriverManager();
    }

    public function test_resolves_driver_from_config(): void
    {
        $driver = $this->manager->resolve('testlog');

        $this->assertInstanceOf(LogDriver::class, $driver);
    }

    public function test_caches_resolved_instance(): void
    {
        $first  = $this->manager->resolve('testlog');
        $second = $this->manager->resolve('testlog');

        $this->assertSame($first, $second);
    }

    public function test_extend_overrides_config_resolution(): void
    {
        $custom = new LogDriver();

        $this->manager->extend('testlog', fn ($app) => $custom);

        $result = $this->manager->resolve('testlog');

        $this->assertSame($custom, $result);
    }

    public function test_extend_invalidates_cache(): void
    {
        // Resolve once to populate cache
        $this->manager->resolve('testlog');

        $custom = new LogDriver();
        $this->manager->extend('testlog', fn ($app) => $custom);

        $result = $this->manager->resolve('testlog');

        $this->assertSame($custom, $result);
    }

    public function test_unknown_driver_throws_config_not_found(): void
    {
        $this->expectException(ConfigNotFoundException::class);

        $this->manager->resolve('nonexistent');
    }

    public function test_missing_class_key_throws_config_mal_formed(): void
    {
        config(['laravel-messenger.drivers.noclass' => ['enabled' => true]]);

        $this->expectException(ConfigMalFormedException::class);

        $this->manager->resolve('noclass');
    }

    public function test_nonexistent_class_throws_driver_class_not_found(): void
    {
        config(['laravel-messenger.drivers.badclass' => [
            'class' => 'App\\Nonexistent',
        ]]);

        $this->expectException(DriverClassNotFoundException::class);

        $this->manager->resolve('badclass');
    }

    public function test_close_all_calls_close_on_persistent_drivers(): void
    {
        $closed = false;

        $fakeDriver = new class ($closed) implements Driver, HasPersistentConnection {
            public function __construct(private bool &$closed) {}

            public function send(Message $message, array $config): void {}

            public function close(): void
            {
                $this->closed = true;
            }
        };

        $this->manager->extend('testlog', fn ($app) => $fakeDriver);
        $this->manager->resolve('testlog');

        $this->manager->closeAll();

        $this->assertTrue($closed, 'close() was not called on persistent driver');
    }
}
