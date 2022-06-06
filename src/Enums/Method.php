<?php

namespace Sammyjo20\Saloon\Enums;

enum Method: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';

    /**
     * Attempt to find an enum for the given value.
     *
     * @param string $value
     * @return static
     */
    public static function upperFrom(string $value): self
    {
        return self::from(mb_strtoupper($value));
    }
}
