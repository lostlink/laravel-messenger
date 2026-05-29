<?php

namespace Lostlink\Messenger;

use Closure;
use Lostlink\Messenger\Contracts\Driver;
use Lostlink\Messenger\Contracts\HasPersistentConnection;
use Lostlink\Messenger\Exceptions\ConfigMalFormedException;
use Lostlink\Messenger\Exceptions\ConfigNotFoundException;
use Lostlink\Messenger\Exceptions\DriverClassNotFoundException;

class DriverManager
{
    private array $customCreators = [];

    private array $resolved = [];

    public function resolve(string $name): Driver
    {
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        if (isset($this->customCreators[$name])) {
            return $this->resolved[$name] = ($this->customCreators[$name])(app());
        }

        $config = config("laravel-messenger.drivers.{$name}", []);

        if (empty($config)) {
            throw new ConfigNotFoundException("Config for driver \"{$name}\" not found");
        }

        if (empty($config['class'])) {
            throw new ConfigMalFormedException("Config for driver \"{$name}\" is missing the required class key");
        }

        if (! class_exists($config['class'])) {
            throw new DriverClassNotFoundException("Class for driver \"{$name}\" not found");
        }

        return $this->resolved[$name] = app($config['class']);
    }

    public function extend(string $name, Closure $factory): void
    {
        $this->customCreators[$name] = $factory;
        unset($this->resolved[$name]);
    }

    public function closeAll(): void
    {
        foreach ($this->resolved as $driver) {
            if ($driver instanceof HasPersistentConnection) {
                $driver->close();
            }
        }
    }

    public function getConfig(string $name): array
    {
        return config("laravel-messenger.drivers.{$name}", []);
    }
}
