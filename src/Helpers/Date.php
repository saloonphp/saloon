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
     */
    public function __construct(protected DateTime $dateTime)
    {
        //
    }

    /**
     * Construct
     */
    public static function now(): static
    {
        return new static(new DateTime);
    }

    /**
     * Add seconds
     *
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
     */
    public function toDateTime(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->dateTime);
    }
}
