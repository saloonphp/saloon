<?php

namespace Sammyjo20\Saloon\Helpers;

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
        return self::is(self::start($pattern, '*'), $value);
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
        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $endpoint;
    }

    /**
     * Check if the url pattern matches the value
     *
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    public static function is(string $pattern, string $value): bool
    {
        if ($pattern === $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);

        if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Give a url a prefix
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
    }
}
