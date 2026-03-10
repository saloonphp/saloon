<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use InvalidArgumentException;

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
     *
     * When the connector has a base URL, the endpoint must be a relative path (e.g. "/users" or "users").
     * Absolute URLs in the endpoint are not allowed in that case (SSRF / credential leakage).
     * When the base URL is empty (e.g. Solo Request), the endpoint may be an absolute URL.
     *
     * @throws InvalidArgumentException When the endpoint is an absolute URL and the base URL is not empty
     */
    public static function join(string $baseUrl, string $endpoint): string
    {
        $baseTrimmed = trim($baseUrl, '/ ');
        if ($baseTrimmed !== '' && static::isValidUrl($endpoint)) {
            throw new InvalidArgumentException(
                'Absolute URLs are not allowed in the endpoint. The endpoint must be a relative path to prevent SSRF and credential leakage. To request a different host, use a connector with that host as the base URL.'
            );
        }

        if ($baseTrimmed === '' && static::isValidUrl($endpoint)) {
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
        // The following str_replace is used to get around an issue raised by PHP 8.4
        // @see https://github.com/php/php-src/issues/17842

        $url = str_replace('_', '-', $url);

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
