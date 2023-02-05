<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Data\Pipe;
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
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function pipe(callable $callable, bool $prepend = false, ?string $name = null): static
    {
        $pipe = new Pipe($callable, $name);

        if (is_string($name) && $this->pipeExists($name)) {
            throw new DuplicatePipeNameException($name);
        }

        $prepend === true
            ? array_unshift($this->pipes, $pipe)
            : $this->pipes[] = $pipe;

        return $this;
    }

    /**
     * Process the pipeline.
     *
     * @param mixed $payload
     * @return mixed
     */
    public function process(mixed $payload): mixed
    {
        foreach ($this->pipes as $pipe) {
            $payload = call_user_func($pipe->callable, $payload);
        }

        return $payload;
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
            $this->pipe($pipe->callable, false, $pipe->name);
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
     *
     * @param string $name
     * @return bool
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
