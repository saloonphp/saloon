<?php

namespace Sammyjo20\Saloon\Traits;

trait HasMake
{
    /**
     * Instantiate a new class with the arguments.
     *
     * @return static
     */
    public static function make(...$arguments): self
    {
        return new static(...$arguments);
    }
}
