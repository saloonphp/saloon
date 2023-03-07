<?php

namespace Saloon\Http\RateLimiting;

use ReflectionClass;
use Saloon\Contracts\Connector;
use Saloon\Helpers\Date;

class Limit
{
    /**
     * Connector string
     *
     * @var string
     */
    protected string $connectorName;

    protected ?string $customId = null;

    protected int $hits = 0;

    protected bool $reachedLimitManually = false;

    protected int $allow;

    protected float $threshold;

    protected ?int $expiryTimestamp = null;

    protected int $releaseInSeconds;

    public function __construct(int $allow, float $threshold = 0.95)
    {
        $this->allow = $allow;
        $this->threshold = $threshold;
    }

    public static function allow(int $allow, float $threshold = 0.95): static
    {
        return new self($allow, $threshold);
    }

    /**
     * This method is run
     *
     * @param int $hits
     * @param int|null $expiryTimestamp
     * @return $this
     */
    public function hydrateFromStore(int $hits = 0, int $expiryTimestamp = null): static
    {
        $this->hits = $hits;
        $this->expiryTimestamp = $expiryTimestamp;

        return $this;
    }

    public function exceeded($releaseInSeconds = null): void
    {
        $this->reachedLimitManually = true;

        if (isset($releaseInSeconds)) {
            $this->releaseInSeconds = $releaseInSeconds;
        }
    }

    public function hasReachedLimit(): bool
    {
        return $this->reachedLimitManually === true || $this->isWithinThreshold();
    }

    /**
     * Check if we are within the threshold
     *
     * @return bool
     */
    public function isWithinThreshold(): bool
    {
        return $this->hits >= ($this->threshold * $this->allow);
    }

    public function getReleaseInSeconds(): int
    {
        return $this->releaseInSeconds;
    }

    public function get(): int
    {
        return $this->hits;
    }

    public function set(int $amount): static
    {
        $this->hits = $amount;

        return $this;
    }

    public function hit(int $amount = 1): static
    {
        $this->hits += $amount;

        if (is_null($this->expiryTimestamp)) {
            $this->expiryTimestamp = Date::now()->addSeconds($this->releaseInSeconds)->toDateTime()->getTimestamp();
        }

        return $this;
    }

    public function getId(): string
    {
        return $this->customId ?? sprintf('%s_a:%sr:%s', $this->connectorName, $this->allow, $this->releaseInSeconds);

        // Todo: Calculate ID based on allow + release in seconds
    }

    public function everyMinute(): static
    {
        $this->releaseInSeconds = 60;

        return $this;
    }

    /**
     * @param \Saloon\Contracts\Connector $connector
     * @return $this
     * @throws \ReflectionException
     */
    public function setConnectorName(Connector $connector): Limit
    {
        $this->connectorName = (new ReflectionClass($connector::class))->getShortName();

        return $this;
    }

    // Todo: Add methods like everyMinute / everyHour / everyDay etc
}
