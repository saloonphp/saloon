<?php

declare(strict_types=1);

namespace Saloon\Http\RateLimiting;

use InvalidArgumentException;
use ReflectionClass;
use Saloon\Helpers\Date;
use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

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

    protected bool $untilMidnight = false;

    public function __construct(int $allow, float $threshold = 1)
    {
        if ($threshold < 0 || $threshold > 1) {
            throw new InvalidArgumentException('Threshold must be a float between 0 and 1. For example a 85% threshold would be 0.85.');
        }

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

    public function hit(int $amount = 1): static
    {
        // Todo: If we have exceeded limit $exceededLimit === true then we ignore any additional hits

        $this->hits += $amount;

        return $this;
    }

    public function getId(): string
    {
        return $this->customId ?? sprintf('%s_a:%sr:%s', $this->objectName, $this->allow, $this->untilMidnight ? 'midnight' : $this->releaseInSeconds);
    }

    /**
     * with a custom id
     *
     * @param string|null $id
     * @return $this
     */
    public function id(?string $id): Limit
    {
        $this->customId = $id;

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
    public function setExpiryTimestamp(?int $expiryTimestamp): static
    {
        $this->expiryTimestamp = $expiryTimestamp;

        return $this;
    }

    public function everySeconds(int $seconds): static
    {
        $this->releaseInSeconds = $seconds;

        return $this;
    }

    public function everyMinute(): static
    {
        return $this->everySeconds(60);
    }

    public function everyFiveMinutes(): static
    {
        return $this->everySeconds(60 * 5);
    }

    public function everyThirtyMinutes(): static
    {
        return $this->everySeconds(60 * 30);
    }

    public function everyHour(): static
    {
        return $this->everySeconds(60 * 60);
    }

    public function everySixHours(): static
    {
        return $this->everySeconds(60 * 60 * 6);
    }

    public function everyTwelveHours(): static
    {
        return $this->everySeconds(60 * 60 * 12);
    }

    public function everyDay(): static
    {
        return $this->everySeconds(60 * 60 * 24);
    }

    public function untilMidnightTonight(): static
    {
        $this->untilMidnight = true;

        return $this->everySeconds(strtotime('tomorrow') - time());
    }

    /**
     * Set the properties from an encoded string
     *
     * @param string $properties
     * @return $this
     * @throws \JsonException
     */
    public function unserializeStoreData(string $properties): static
    {
        $values = json_decode($properties, true, 512, JSON_THROW_ON_ERROR);

        if (isset($values['timestamp'])) {
            $this->setExpiryTimestamp($values['timestamp']);
        }

        if (isset($values['hits'])) {
            $this->hit($values['hits']);
        }

        return $this;
    }

    /**
     * Get the encoded properties to be stored
     *
     * @return string
     * @throws \JsonException
     */
    public function serializeStoreData(): string
    {
        return json_encode([
            'timestamp' => $this->getExpiryTimestamp(),
            'hits' => $this->getHits(),
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Get the remaining time in seconds
     *
     * @return int
     */
    public function getRemainingSeconds(): int
    {
        $now = Date::now()->toDateTime()->getTimestamp();

        return (int)round($this->getExpiryTimestamp() - $now);
    }
}
