<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class FixedClock implements ClockInterface
{
    /**
     * Constructor
     */
    public function __construct(protected DateTimeImmutable $now)
    {
        //
    }

    /**
     * Get the current time.
     */
    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
