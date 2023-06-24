<?php

declare(strict_types=1);

namespace Saloon\Helpers;

class FixtureHelper
{
    /**
     * Recursively replaces array attributes
     *
     * @param array<string, mixed> $source
     * @param array<string, mixed> $rules
     * @param bool $caseSensitiveKeys
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
}
