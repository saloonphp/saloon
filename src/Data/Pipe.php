<?php

declare(strict_types=1);

namespace Saloon\Data;

use Closure;
use Saloon\Enums\PipeOrder;

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
        public readonly ?string    $name = null,
        public readonly ?PipeOrder $order = null,
    ) {
        $this->callable = $callable(...);
    }
}
