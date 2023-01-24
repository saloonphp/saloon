<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface Conditionable
{
    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @template TValue
     *
     * @param TValue $value
     * @param callable($this, TValue): void $callback
     * @param (callable($this, TValue): void)|null $default
     * @return $this
     */
    public function when(mixed $value, callable $callback, callable|null $default = null): static;

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @template TValue
     *
     * @param TValue $value
     * @param callable($this, TValue): void $callback
     * @param (callable($this, TValue): void)|null $default
     * @return $this
     */
    public function unless(mixed $value, callable $callback, callable|null $default = null): static;
}
