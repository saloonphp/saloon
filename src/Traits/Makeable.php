<?php

declare(strict_types=1);

namespace Saloon\Traits;

trait Makeable
{
    /**
     * Instantiate a new class with the arguments.
     *
     * @param ...$arguments
     * @return static
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
