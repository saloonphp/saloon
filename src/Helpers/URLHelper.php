<?php

declare(strict_types=1);

namespace Saloon\Helpers;

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
        if (static::isValidUrl($endpoint)) {
            return $endpoint;
        }

        if ($endpoint !== '/') {
            $endpoint = ltrim($endpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($endpoint) && $endpoint !== '/';

        $baseEndpoint = rtrim($baseUrl, '/ ');

        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $endpoint;
    }

    /**
     * Check if the URL is a valid URL
     *
     * @param string $url
     * @return bool
     */
    public static function isValidUrl(string $url): bool
    {
        return ! empty(filter_var($url, FILTER_VALIDATE_URL));
    }
}
