<?php

declare(strict_types=1);

namespace Saloon\Data;

use Closure;

class Pipe
{
    public readonly Closure $callable;

    /**
     * Constructor
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @param string|null $name
     */
    public function __construct(
        callable $callable,
        readonly public ?string $name = null,
    ) {
        $this->callable = $callable(...);
    }
}
