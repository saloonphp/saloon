<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use InvalidArgumentException;
use Saloon\Contracts\DebuggingDriver;

class Debugger
{
    /**
     * @var array<string, \Saloon\Contracts\DebuggingDriver>
     */
    protected static array $registeredDrivers = [];

    /**
     * @var array<string, bool>
     */
    protected array $useDrivers = [];

    /**
     * @var bool
     */
    protected bool $beforeSent = false;

    /**
     * @var bool
     */
    protected bool $afterSent = false;

    /**
     * @param \Saloon\Contracts\DebuggingDriver $driver
     *
     * @return $this
     */
    public static function registerDriver(DebuggingDriver $driver): static
    {
        static::$registeredDrivers[$driver->name()] = $driver;

        return new static;
    }

    /**
     * @param \Saloon\Contracts\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     */
    public function usingDriver(DebuggingDriver|string $driver): static
    {
        // Todo: Throw an exception if the string-based driver (not class) does not exist.
        // Todo: Also make sure to implode the array_keys of the registered drivers so you get a nice error
        // Todo: message like: "Available drivers: ray, syslog, laravel" etc

        if ($driver instanceof DebuggingDriver) {
            static::registerDriver($driver);
        }

        $driverName = is_string($driver) ? $driver : $driver->name();

        $this->useDrivers[$driverName] = true;

        return $this;
    }

    /**
     * @param \Saloon\Contracts\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     */
    public function withoutDriver(DebuggingDriver|string $driver): static
    {
        $driverName = is_string($driver) ? $driver : $driver->name();

        unset($this->useDrivers[$driverName]);

        return $this;
    }

    /**
     * @param \Saloon\Contracts\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     */
    public function onlyDriver(DebuggingDriver|string $driver): static
    {
        $this->useDrivers = [];

        $this->usingDriver($driver);

        return $this;
    }

    /**
     * @param bool $beforeSent
     *
     * @return $this
     */
    public function beforeSent(bool $beforeSent = true): static
    {
        $this->beforeSent = $beforeSent;

        return $this;
    }

    /**
     * @param bool $afterSent
     *
     * @return $this
     */
    public function afterSent(bool $afterSent = true): static
    {
        $this->afterSent = $afterSent;

        return $this;
    }

    /**
     * Before and after sent
     *
     * @return $this
     */
    public function beforeAndAfterSent(bool $beforeAndAfterSent = true): static
    {
        return $this->beforeSent($beforeAndAfterSent)->afterSent($beforeAndAfterSent);
    }

    /**
     * @param string $name
     * @param array<string, mixed> $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments): static
    {
        if (str_starts_with($name, 'using')) {
            return $this->usingDriver(strtolower(substr($name, 5)));
        }

        if (str_starts_with($name, 'only')) {
            return $this->onlyDriver(strtolower(substr($name, 4)));
        }

        // TODO: Throw a MethodNotFound exception
        throw new InvalidArgumentException;
    }

    /**
     * @param \Saloon\Debugging\DebugData $data
     *
     * @return $this
     */
    public function send(DebugData $data): static
    {
        foreach ($this->useDrivers as $driverName => $shouldUse) {
            if (! $shouldUse) {
                continue;
            }

            if ($this->beforeSent === true && $data->wasNotSent()) {
                static::$registeredDrivers[$driverName]->send($data);
                continue;
            }

            if ($this->afterSent === true && $data->wasSent()) {
                static::$registeredDrivers[$driverName]->send($data);
                continue;
            }
        }

        return $this;
    }
}
