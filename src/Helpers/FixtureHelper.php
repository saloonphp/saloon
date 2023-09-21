<?php

declare(strict_types=1);

namespace Saloon\Helpers;

/**
 * @internal
 */
class FixtureHelper
{
    /**
     * Recursively replaces array attributes
     *
     * @param array<string, mixed> $source
     * @param array<string, mixed> $rules
     * @return array<string, mixed>
     */
    public static function recursivelyReplaceAttributes(array $source, array $rules, bool $caseSensitiveKeys = true): array
    {
        if ($caseSensitiveKeys === false) {
            $rules = array_change_key_case($rules, CASE_LOWER);
        }

        array_walk_recursive($source, static function (&$value, $key) use ($rules, $caseSensitiveKeys) {
            if ($caseSensitiveKeys === false) {
                $key = mb_strtolower($key);
            }

            if (! array_key_exists($key, $rules)) {
                return;
            }

            $swappedValue = $rules[$key];

            $value = is_callable($swappedValue) ? $swappedValue($value) : $swappedValue;
        });

        return $source;
    }

    /**
     * Replace sensitive regex patterns
     *
     * @param array<string, string> $patterns
     */
    public static function replaceSensitiveRegexPatterns(string $source, array $patterns): string
    {
        foreach ($patterns as $pattern => $replacement) {
            $matches = [];

            preg_match_all($pattern, $source, $matches);

            $matches = array_unique($matches[0] ?? []);

            foreach ($matches as $match) {
                $value = is_callable($replacement) ? $replacement($match) : $replacement;

                $source = str_replace($match, $value, $source);
            }
        }

        return $source;
    }
}
