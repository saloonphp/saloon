<?php

namespace Sammyjo20\Saloon\Helpers;

use ReflectionClass;
use ReflectionException;
use Sammyjo20\Saloon\Exceptions\InvalidRequestKeyException;

class ProxyRequestNameHelper
{
    /**
     * Recursively generate the names of requests.
     *
     * @param array $requests
     * @return array
     * @throws InvalidRequestKeyException
     * @throws ReflectionException
     */
    public static function generateNames(array $requests): array
    {
        $guessed = [];
        foreach ($requests as $key => $value) {
            if (is_array($value)) {
                $value = static::generateNames($value);
            }

            if (is_string($key)) {
                $guessed[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                throw new InvalidRequestKeyException('Request groups must be keyed.');
            }

            $name = (new ReflectionClass($value))->getShortName();
            $words = explode(' ', str_replace(['-', '_'], ' ', $name));
            $studlyWords = array_map(fn ($word) => ucfirst($word), $words);
            $guessedKey = lcfirst(implode($studlyWords));

            $guessed[$guessedKey] = $value;
        }

        return $guessed;
    }
}
