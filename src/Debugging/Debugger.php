<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use InvalidArgumentException;
use Saloon\Contracts\DebuggingDriver;
use Saloon\Debugging\Drivers\ErrorLogDebugger;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\SystemLogDebugger;

class Debugger
{
    /**
     * @var array<string, \Saloon\Contracts\DebuggingDriver>
     */
    protected static array $globalRegisteredDrivers = [];

    /**
     * @var array<string, \Saloon\Contracts\DebuggingDriver>
     */
    protected array $registeredDrivers = [];

    /**
     * @var array<string, bool>
     */
    protected array $useDrivers = [];

    /**
     * @var bool
     */
    protected bool $showRequest = false;

    /**
     * @var bool
     */
    protected bool $showResponse = false;

    public function __construct()
    {
        $this->registerDriver(new RayDebugger);
        $this->registerDriver(new ErrorLogDebugger);
        $this->registerDriver(new SystemLogDebugger);
    }

    /**
     * @param \Saloon\Contracts\DebuggingDriver $driver
     *
     * @return $this
     */
    public static function registerGlobalDriver(DebuggingDriver $driver): static
    {
        static::$globalRegisteredDrivers[$driver->name()] = $driver;

        return new static;
    }

    /**
     * @param \Saloon\Contracts\DebuggingDriver $driver
     *
     * @return $this
     */
    public function registerDriver(DebuggingDriver $driver): static
    {
        $this->registeredDrivers[$driver->name()] = $driver;

        return $this;
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
            $this->registerDriver($driver);
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
     * @param bool $showRequest
     *
     * @return $this
     */
    public function showRequest(bool $showRequest = true): static
    {
        $this->showRequest = $showRequest;

        return $this;
    }

    /**
     * @param bool $showResponse
     *
     * @return $this
     */
    public function showResponse(bool $showResponse = true): static
    {
        $this->showResponse = $showResponse;

        return $this;
    }

    /**
     * Before and after sent
     *
     * @return $this
     */
    public function showRequestAndResponse(bool $showRequestAndResponse = true): static
    {
        return $this->showRequest($showRequestAndResponse)->showResponse($showRequestAndResponse);
    }

    /**
     * Get the registered drivers
     *
     * @return array
     */
    protected function getRegisteredDrivers(): array
    {
        return [
            ...static::$globalRegisteredDrivers,
            ...$this->registeredDrivers,
        ];
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

            $registeredDrivers = $this->getRegisteredDrivers();

            if ($this->showRequest === true && $data->wasNotSent()) {
                $registeredDrivers[$driverName]->send($data);
                continue;
            }

            if ($this->showResponse === true && $data->wasSent()) {
                $registeredDrivers[$driverName]->send($data);
                continue;
            }
        }

        return $this;
    }
}
