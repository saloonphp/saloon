<?php

namespace Sammyjo20\Saloon\Helpers;

use League\Pipeline\Pipeline as BasePipeline;

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
     * @param bool $highPriority
     * @return $this
     */
    public function pipe(callable $pipe, bool $highPriority = false): self
    {
        if ($highPriority === true) {
            array_unshift($this->pipes, $pipe);
        } else {
            $this->pipes[] = $pipe;
        }

        $this->pipes = array_values($this->pipes);

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
        $basePipeline = new BasePipeline;

        foreach ($this->pipes as $pipe) {
            $basePipeline = $basePipeline->pipe($pipe);
        }

        return $basePipeline->process($payload);
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