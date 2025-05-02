<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use ArrayAccess;

use function is_string;

/**
 * @internal
 */
final class ArrayHelpers
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @phpstan-assert-if-true array<array-key, mixed>|ArrayAccess<array-key, mixed> $value
     */
    private static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array<array-key, mixed>|ArrayAccess<array-key, mixed> $array
     * @param array-key|float $key
     */
    private static function exists(array|ArrayAccess $array, string|int|float $key): bool
    {
        if (is_float($key)) {
            $key = (string)$key;
        }

        return $array instanceof ArrayAccess
            ? $array->offsetExists($key)
            : array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array<array-key, mixed> $array
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public static function get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (! is_string($key) || ! str_contains($key, '.')) {
            return $array[$key] ?? Helpers::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return Helpers::value($default);
            }
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array<array-key, mixed> $array
     * @param string|int|null $key
     * @param mixed $value
     * @return array<array-key, mixed>
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', (string)$key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
