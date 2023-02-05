<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Helpers\Helpers;

trait Conditionable
{
    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @template TValue
     *
     * @param \Closure(): (TValue)|TValue $value
     * @param callable($this, TValue): (void) $callback
     * @param callable($this, TValue): (void)|null $default
     * @return $this
     */
    public function when(mixed $value, callable $callback, callable|null $default = null): static
    {
        $value = Helpers::value($value, $this);

        if ($value) {
            $callback($this, $value);

            return $this;
        }

        if ($default) {
            $default($this, $value);
        }

        return $this;
    }

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @template TValue
     *
     * @param \Closure(): (TValue)|TValue $value
     * @param callable($this, TValue): (void) $callback
     * @param callable($this, TValue): (void)|null $default
     * @return $this
     */
    public function unless(mixed $value, callable $callback, callable|null $default = null): static
    {
        $value = Helpers::value($value, $this);

        if (! $value) {
            $callback($this, $value);

            return $this;
        }

        if ($default) {
            $default($this, $value);
        }

        return $this;
    }
}
