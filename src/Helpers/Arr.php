<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use ArrayAccess;

use function is_string;

class Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     *
     * @phpstan-assert-if-true array|ArrayAccess $value
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array<array-key, mixed>|ArrayAccess<array-key, mixed> $array
     * @param array-key|float $key
     * @return bool
     */
    public static function exists(array|ArrayAccess $array, string|int|float $key): bool
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
     * @param mixed|null $default
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public static function get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        if (! static::accessible($array)) {
            return Helpers::value($default);
        }

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
     * Map an array with keys
     *
     * @template TKey of array-key
     * @template TValue
     * @template TReturnKey of array-key
     * @template TReturnValue
     *
     * @param array<TKey, TValue> $items
     * @param callable(TValue, TKey): (array<TReturnKey, TReturnValue>) $callback
     * @return array<TReturnKey, TReturnValue>
     */
    public static function mapWithKeys(array $items, callable $callback): array
    {
        $result = [];

        foreach ($items as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }
}
