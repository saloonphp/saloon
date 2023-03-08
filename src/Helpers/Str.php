<?php

declare(strict_types=1);

namespace Saloon\Helpers;

class Str
{
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string|iterable<string> $pattern
     * @param string $value
     * @return bool
     */
    public static function is(string|iterable $pattern, string $value): bool
    {
        if (! is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|iterable<string> $needles
     * @return bool
     */
    public static function endsWith(string $haystack, string|iterable $needles): bool
    {
        if (! is_iterable($needles)) {
            $needles = (array)$needles;
        }

        foreach ($needles as $needle) {
            if ((string)$needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int<1, max> $length
     * @return string
     * @throws \Exception
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = mb_strlen($string)) < $length) {
            /** @var int<1, max> $size */
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= mb_substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
