<?php

declare(strict_types=1);

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
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return $this
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static
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
