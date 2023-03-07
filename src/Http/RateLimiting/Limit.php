<?php

namespace Saloon\Http\RateLimiting;

use ReflectionClass;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Helpers\Date;

class Limit
{
    /**
     * Connector string
     *
     * @var string
     */
    protected string $objectName;

    protected ?string $customId = null;

    protected int $hits = 0;

    protected int $allow;

    protected float $threshold;

    protected ?int $expiryTimestamp = null;

    protected int $releaseInSeconds;

    public function __construct(int $allow, float $threshold = 0.95)
    {
        // Todo: Build protections into here to prevent limiters being used that haven't been properly setup

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
        $this->hits = $this->allow;

        if (isset($releaseInSeconds)) {
            $this->expiryTimestamp = Date::now()->addSeconds($releaseInSeconds)->toDateTime()->getTimestamp();
        }
    }

    public function hasReachedLimit(): bool
    {
        return $this->hits >= ($this->threshold * $this->allow);
    }

    public function getReleaseInSeconds(): int
    {
        return $this->releaseInSeconds;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $amount): static
    {
        $this->hits = $amount;

        return $this;
    }

    public function hit(int $amount = 1): static
    {
        $this->hits += $amount;

        return $this;
    }

    public function getId(): string
    {
        return $this->customId ?? sprintf('%s_a:%sr:%s', $this->objectName, $this->allow, $this->releaseInSeconds);
    }

    /**
     * with a custom id
     *
     * @param string|null $id
     * @return $this
     */
    public function withId(?string $id): Limit
    {
        $this->customId = $id;

        return $this;
    }

    public function everyMinute(): static
    {
        $this->releaseInSeconds = 60;

        return $this;
    }

    /**
     * @param \Saloon\Contracts\Connector|\Saloon\Contracts\Request $object
     * @return $this
     * @throws \ReflectionException
     */
    public function setObjectName(Connector|Request $object): Limit
    {
        $this->objectName = (new ReflectionClass($object::class))->getShortName();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiryTimestamp(): ?int
    {
        return $this->expiryTimestamp ??= Date::now()->addSeconds($this->releaseInSeconds)->toDateTime()->getTimestamp();
    }

    /**
     * @param int|null $expiryTimestamp
     * @return Limit
     */
    public function setExpiryTimestamp(?int $expiryTimestamp): Limit
    {
        $this->expiryTimestamp = $expiryTimestamp;
        return $this;
    }

    // Todo: Add methods like everyMinute / everyHour / everyDay etc
}
