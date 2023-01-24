<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use DateTime;
use DateInterval;
use DateTimeImmutable;

class Date
{
    /**
     * Constructor
     *
     * @param \DateTime $dateTime
     */
    public function __construct(protected DateTime $dateTime)
    {
        //
    }

    /**
     * Construct
     *
     * @return static
     */
    public static function now(): static
    {
        return new static(new DateTime);
    }

    /**
     * Add seconds
     *
     * @param int $seconds
     * @return $this
     */
    public function addSeconds(int $seconds): static
    {
        $this->dateTime->add(
            DateInterval::createFromDateString($seconds . ' seconds')
        );

        return $this;
    }

    /**
     * Subtract minutes
     *
     * @param int $minutes
     * @return $this
     */
    public function subMinutes(int $minutes): static
    {
        $this->dateTime->sub(
            DateInterval::createFromDateString($minutes . ' minutes')
        );

        return $this;
    }

    /**
     * Get the datetime instance
     *
     * @return \DateTimeImmutable
     */
    public function toDateTime(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->dateTime);
    }
}
