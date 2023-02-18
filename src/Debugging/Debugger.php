<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use InvalidArgumentException;
use Saloon\Debugging\Drivers\DebuggingDriver;

class Debugger
{
    /**
     * @var array<string, \Saloon\Debugging\Drivers\DebuggingDriver>
     */
    protected array $registeredDrivers = [];

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
     * @param \Saloon\Debugging\Drivers\DebuggingDriver $driver
     *
     * @return $this
     */
    public function registerDriver(DebuggingDriver $driver): static
    {
        $this->registeredDrivers[ $driver->name() ] = $driver;

        return $this;
    }

    /**
     * @param \Saloon\Debugging\Drivers\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     */
    public function usingDriver(DebuggingDriver|string $driver): static
    {
        if ($driver instanceof DebuggingDriver) {
            $this->registerDriver($driver);
        }

        $driverName = is_string($driver) ? $driver : $driver->name();

        $this->useDrivers[ $driverName ] = true;

        return $this;
    }

    /**
     * @param \Saloon\Debugging\Drivers\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
     *
     * @return $this
     */
    public function withoutDriver(DebuggingDriver|string $driver): static
    {
        $driverName = is_string($driver) ? $driver : $driver->name();

        unset($this->useDrivers[ $driverName ]);

        return $this;
    }

    /**
     * @param \Saloon\Debugging\Drivers\DebuggingDriver|string $driver A DebuggingDriver or the name one of a registered one.
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
     * @param string $name
     * @param array<string, mixed> $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'using')) {
            return $this->usingDriver(
                strtolower(substr($name, 5)),
            );
        }

        if (str_starts_with($name, 'only')) {
            return $this->onlyDriver(
                strtolower(substr($name, 4)),
            );
        }

        // TODO: How should we handle this?
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

            if ($data->wasNotSent() && $this->beforeSent) {
                $this->registeredDrivers[ $driverName ]->send($data);
                continue;
            }

            if ($data->wasSent() && $this->afterSent) {
                $this->registeredDrivers[ $driverName ]->send($data);
                continue;
            }
        }

        return $this;
    }
}
