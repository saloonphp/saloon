<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsHandlers
{
    /**
     * All the handlers loaded
     *
     * @var array
     */
    protected array $handlers = [];

    /**
     * Add a handler
     *
     * @param string $name
     * @param callable $function
     * @return void
     */
    public function addHandler(string $name, callable $function): void
    {
        $this->handlers[$name] = $function;
    }

    /**
     * Merge all the handlers into one array.
     *
     * @param array ...$handlersCollection
     * @return $this
     */
    public function mergeHandlers(array ...$handlersCollection): static
    {
        foreach ($handlersCollection as $handler) {
            $this->handlers = array_merge($this->handlers, $handler);
        }

        return $this;
    }

    /**
     * Return all the handlers
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
