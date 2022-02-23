<?php

namespace Sammyjo20\Saloon\Traits;

trait HasMake
{
    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     * @return \Sammyjo20\Saloon\Http\SaloonConnector|\Sammyjo20\Saloon\Http\SaloonRequest
     */
    public static function make(...$arguments): self
    {
        return new static(...$arguments);
    }
}
