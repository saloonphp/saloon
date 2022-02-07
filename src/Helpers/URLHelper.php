<?php

namespace Sammyjo20\Saloon\Helpers;

use Illuminate\Support\Str;

class URLHelper
{
    /**
     * Check if a URL matches a given pattern
     *
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    public static function matches(string $pattern, string $value): bool
    {
        return Str::is(Str::start($pattern, '*'), $value);
    }
}
