<?php

namespace Sammyjo20\Saloon\Helpers;

use ArrayAccess;

class Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param string|int|float $key
     * @return bool
     */
    public static function exists(array $array, string|int|float $key): bool
    {
        if (is_float($key)) {
            $key = (string)$key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string|int|null $key
     * @param mixed|null $default
     * @return mixed
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

        if (! str_contains($key, '.')) {
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
}
