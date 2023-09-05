<?php

declare(strict_types=1);

namespace Saloon\Data;

use Closure;

class Pipe
{
    /**
     * The callable inside the pipe
     */
    public readonly Closure $callable;

    /**
     * Constructor
     *
     * @param callable(mixed $payload): (mixed) $callable
     */
    public function __construct(
        callable                   $callable,
        readonly public ?string    $name = null,
        readonly public ?PipeOrder $order = null,
    ) {
        $this->callable = $callable(...);
    }
}
