<?php

namespace Sammyjo20\Saloon\Helpers;

use Closure;

class Helpers
{
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|string $class
     * @return array
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
     * @param string $trait
     * @return array
     */
    public static function traitUsesRecursive(string $trait): array
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += static::traitUsesRecursive($trait);
        }

        return $traits;
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param object|string $class
     * @return string
     */
    public static function classBasename(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public static function value(mixed $value, ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
