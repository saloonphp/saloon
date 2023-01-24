<?php

declare(strict_types=1);

namespace Saloon\Data;

class Pipe
{
    /**
     * Constructor
     *
     * @param callable $callable
     * @param string|null $name
     */
    public function __construct(
        readonly public mixed $callable,
        readonly public ?string $name = null,
    ) {
        //
    }
}
