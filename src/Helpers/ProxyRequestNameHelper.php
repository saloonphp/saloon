<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use ReflectionClass;
use Illuminate\Support\Collection;
use Saloon\Exceptions\InvalidRequestKeyException;

class ProxyRequestNameHelper
{
    /**
     * Recursively generate the names of requests.
     *
     * @param array $requests
     * @return array
     * @throws InvalidRequestKeyException
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

            if (is_array($value)) {
                throw new InvalidRequestKeyException('Request groups must be keyed.');
            }

            $guessedKey = Str::camel((new ReflectionClass($value))->getShortName());

            return [$guessedKey => $value];
        })->toArray();
    }
}
