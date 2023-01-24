<?php

declare(strict_types=1);

namespace Saloon\Traits;

trait Makeable
{
    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     * @return static
     */
    public static function make(mixed ...$arguments): static
    {
        return new static(...$arguments);
    }
}
