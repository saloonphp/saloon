<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Closure;

class Helpers
{
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|class-string $class
     * @return array<class-string, class-string>
     */
    public static function classUsesRecursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += static::traitUsesRecursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param class-string $trait
     * @return array<class-string, class-string>
     */
    public static function traitUsesRecursive(string $trait): array
    {
        /** @var array<class-string, class-string> $traits */
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += static::traitUsesRecursive($trait);
        }

        return $traits;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public static function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
