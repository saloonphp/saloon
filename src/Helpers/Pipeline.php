<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Exceptions\DuplicatePipeNameException;

class Pipeline
{
    /**
     * The pipes in the pipeline.
     *
     * @var array
     */
    protected array $pipes = [];

    /**
     * The named pipes that have been added.
     *
     * @var array
     */
    protected array $namedPipes = [];

    /**
     * Add a pipe to the pipeline.
     *
     * @param callable $pipe
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function pipe(callable $pipe, bool $prepend = false, ?string $name = null): static
    {
        if ($prepend === true) {
            array_unshift($this->pipes, $pipe);
        } else {
            $this->pipes[] = $pipe;
        }

        if (! is_null($name)) {
            in_array($name, $this->namedPipes, true) ? throw new DuplicatePipeNameException($name) : $this->namedPipes[] = $name;
        }

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
            $payload = $pipe($payload);
        }

        return $payload;
    }

    /**
     * Set the pipes on the pipeline.
     *
     * @param array $pipes
     * @return $this
     */
    public function setPipes(array $pipes): static
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Get all the pipes in the pipeline
     *
     * @return array
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }
}
