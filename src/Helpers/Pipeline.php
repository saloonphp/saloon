<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Data\Pipe;
use Saloon\Enums\Order;
use Saloon\Data\PipeOrder;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Contracts\Pipeline as PipelineContract;

class Pipeline implements PipelineContract
{
    /**
     * The pipes in the pipeline.
     *
     * @var array<\Saloon\Data\Pipe>
     */
    protected array $pipes = [];

    /**
     * Add a pipe to the pipeline
     *
     * @param callable(mixed $payload): (mixed) $callable
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function pipe(callable $callable, ?string $name = null, ?PipeOrder $order = null): static
    {
        $pipe = new Pipe($callable, $name, $order);

        if (is_string($name) && $this->pipeExists($name)) {
            throw new DuplicatePipeNameException($name);
        }

        $this->pipes[] = $pipe;

        return $this;
    }

    /**
     * Process the pipeline.
     */
    public function process(mixed $payload): mixed
    {
        foreach ($this->sortPipes() as $pipe) {
            $payload = call_user_func($pipe->callable, $payload);
        }

        return $payload;
    }

    /**
     * Sort the pipes based on the "order" classes
     */
    protected function sortPipes(): array
    {
        $pipes = $this->pipes;

        /** @var array<\Saloon\Data\PipeOrder> $pipeNames */
        $pipeOrders = array_map(static fn (Pipe $pipe) => $pipe->order, $pipes);

        // Now we'll iterate through the pipe orders and if a specific pipe
        // requests to be placed at the top - we will move the pipe to the
        // top of the array. If it wants to be at the bottom we can put it
        // there too.

        foreach ($pipeOrders as $index => $order) {
            if (is_null($order)) {
                continue;
            }

            $pipe = $pipes[$index];

            unset($pipes[$index]);

            match (true) {
                $order->type === Order::FIRST => array_unshift($pipes, $pipe),
                $order->type === Order::LAST => $pipes[] = $pipe,
            };
        }

        return $pipes;
    }

    /**
     * Set the pipes on the pipeline.
     *
     * @param array<\Saloon\Data\Pipe> $pipes
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function setPipes(array $pipes): static
    {
        $this->pipes = [];

        // Loop through each of the pipes and manually add each pipe
        // so we can check if the name already exists.

        foreach ($pipes as $pipe) {
            $this->pipe($pipe->callable, $pipe->name, $pipe->order);
        }

        return $this;
    }

    /**
     * Get all the pipes in the pipeline
     *
     * @return array<\Saloon\Data\Pipe>
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }

    /**
     * Check if a given pipe exists for a name
     */
    protected function pipeExists(string $name): bool
    {
        foreach ($this->pipes as $pipe) {
            if ($pipe->name === $name) {
                return true;
            }
        }

        return false;
    }
}
