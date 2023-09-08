<?php

declare(strict_types=1);

namespace Saloon\Tests\Helpers;

use DateTime;
use DateInterval;
use DateTimeImmutable;

final class Date
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
    public static function now(): self
    {
        return new self(new DateTime);
    }

    /**
     * Add seconds
     *
     * @return $this
     */
    public function addSeconds(int $seconds): self
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
    public function subMinutes(int $minutes): self
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
