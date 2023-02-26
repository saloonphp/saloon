<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use InvalidArgumentException;
use Saloon\Contracts\DebuggingDriver;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\ErrorLogDebugger;
use Saloon\Debugging\Drivers\SystemLogDebugger;
use Saloon\Exceptions\UnknownDriverException;

class Debugger
{
    /**
     * Globally registered drivers
     *
     * @var array<string, \Saloon\Contracts\DebuggingDriver>
     */
    protected static array $globalRegisteredDrivers = [];

    /**
     * Locally registered drivers
     *
     * @var array<string, \Saloon\Contracts\DebuggingDriver>
     */
    protected array $registeredDrivers = [];

    /**
     * Drivers that have been subscribed to
     *
     * @var array<string, bool>
     */
    protected array $useDrivers = [];

    /**
     * Denotes if we send the request to the debugging driver
     *
     * @var bool
     */
    protected bool $showRequest = false;

    /**
     * Denotes if we send the response to the debugging driver
     *
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
     * Register a driver globally
     *
     * @param \Saloon\Contracts\DebuggingDriver $driver
     *
     * @return static
     */
    public static function registerGlobalDriver(DebuggingDriver $driver): static
    {
        static::$globalRegisteredDrivers[$driver->name()] = $driver;

        return new static;
    }

    /**
     * Register a driver
     *
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
     * Subscribe to a given driver
     *
     * @param \Saloon\Contracts\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     * @throws \Saloon\Exceptions\UnknownDriverException
     */
    public function usingDriver(DebuggingDriver|string $driver): static
    {
        if ($driver instanceof DebuggingDriver) {
            $this->registerDriver($driver);
        }

        $driverName = is_string($driver) ? $driver : $driver->name();

        // We'll validate that the driver exists

        $registeredDriverNames = array_keys($this->getRegisteredDrivers());

        if (! in_array($driverName, $registeredDriverNames, true)) {
            throw new UnknownDriverException(sprintf('Unable to find the "%s" driver. Registered drivers: %s', $driverName, implode(', ', $registeredDriverNames)));
        }

        $this->useDrivers[$driverName] = true;

        return $this;
    }

    /**
     * Unsubscribe from a driver
     *
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
     * Only use a given driver
     *
     * @param \Saloon\Contracts\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     * @throws \Saloon\Exceptions\UnknownDriverException
     */
    public function onlyDriver(DebuggingDriver|string $driver): static
    {
        $this->useDrivers = [];

        $this->usingDriver($driver);

        return $this;
    }

    /**
     * Send the request to the debugging driver.
     *
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
     * Send the response to the debugging driver.
     *
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
     * Send the request and response to the debugging driver.
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
     * @return array<string, \Saloon\Contracts\DebuggingDriver>
     */
    public function getRegisteredDrivers(): array
    {
        return [
            ...static::$globalRegisteredDrivers,
            ...$this->registeredDrivers,
        ];
    }

    /**
     * Send the debugging data to the given driver
     *
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
            }
        }

        return $this;
    }
}
