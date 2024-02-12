<?php

declare(strict_types=1);

namespace Saloon\Helpers;

/**
 * @internal
 */
class URLHelper
{
    /**
     * Check if a URL matches a given pattern
     */
    public static function matches(string $pattern, string $value): bool
    {
        return StringHelpers::matchesPattern(StringHelpers::start($pattern, '*'), $value);
    }

    /**
     * Join a base url and an endpoint together.
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
     */
    public static function isValidUrl(string $url): bool
    {
        return ! empty(filter_var($url, FILTER_VALIDATE_URL));
    }

    /**
     * Parse a query string into an array
     *
     * @return array<string, mixed>
     */
    public static function parseQueryString(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $parameters = [];

        foreach (explode('&', $query) as $parameter) {
            $name = urldecode((string)strtok($parameter, '='));
            $value = urldecode((string)strtok('='));

            if (! $name || str_starts_with($parameter, '=')) {
                continue;
            }

            $parameters[$name] = $value;
        }

        return $parameters;
    }
}
