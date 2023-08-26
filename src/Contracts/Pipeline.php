<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Data\PipeOrder;

interface Pipeline
{
    /**
     * Add a pipe to the pipeline
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function pipe(callable $callable, ?string $name = null, ?PipeOrder $order = null): static;

    /**
     * Process the pipeline.
     */
    public function process(mixed $payload): mixed;

    /**
     * Set the pipes on the pipeline.
     *
     * @param array<\Saloon\Data\Pipe> $pipes
     * @return $this
     */
    public function setPipes(array $pipes): static;

    /**
     * Get all the pipes in the pipeline
     *
     * @return array<\Saloon\Data\Pipe>
     */
    public function getPipes(): array;
}
