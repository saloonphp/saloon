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

    /**
     * Join a base url and an endpoint together.
     *
     * @param string $baseUrl
     * @param string $endpoint
     * @return string
     */
    public static function join(string $baseUrl, string $endpoint): string
    {
        if ($endpoint !== '/') {
            $endpoint = ltrim($endpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($endpoint) && $endpoint !== '/';

        $baseEndpoint = rtrim($baseUrl, '/ ');

        $endpointIsNotUrl = empty(filter_var($endpoint, FILTER_VALIDATE_URL));
        $glue = $endpointIsNotUrl ? '/' : null;

        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . $glue : $baseEndpoint;

        return $baseEndpoint . $endpoint;
    }
}
