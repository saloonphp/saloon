<?php

namespace Saloon\Traits;

use Saloon\Helpers\Helpers;

trait Conditionable
{
    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when(mixed $value, callable $callback, callable $default = null): static
    {
        $value = Helpers::value($value, $this);

        if ($value) {
            return $callback($this, $value) ?? $this;
        }

        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return $this
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static
    {
        $value = Helpers::value($value, $this);

        if (! $value) {
            return $callback($this, $value) ?? $this;
        }

        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }
}
