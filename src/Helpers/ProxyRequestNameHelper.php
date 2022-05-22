<?php

namespace Sammyjo20\Saloon\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class ProxyRequestNameHelper
{
    /**
     * Recursively generate the names of requests.
     *
     * @param array $requests
     * @return array
     * @throws \ReflectionException
     */
    public static function generateNames(array $requests): array
    {
        return (new Collection($requests))->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                $value = static::generateNames($value);
            }

            if (is_string($key)) {
                return [$key => $value];
            }

            $guessedKey = Str::camel((new ReflectionClass($value))->getShortName());

            return [$guessedKey => $value];
        })->toArray();
    }
}
