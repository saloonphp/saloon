<?php

declare(strict_types=1);

namespace Saloon\Helpers;

class Pipeline
{
    /**
     * The pipes in the pipeline.
     *
     * @var array
     */
    protected array $pipes = [];

    /**
     * Add a pipe to the pipeline.
     *
     * @param callable $pipe
     * @param bool $prepend
     * @return $this
     */
    public function pipe(callable $pipe, bool $prepend = false): static
    {
        if ($prepend === true) {
            array_unshift($this->pipes, $pipe);
        } else {
            $this->pipes[] = $pipe;
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
