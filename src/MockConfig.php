<?php

declare(strict_types=1);

namespace Saloon;

final class MockConfig
{
    /**
     * Default fixture path
     */
    private static string $fixturePath = 'tests/Fixtures/Saloon';

    /**
     * Denotes if an exception should be thrown if a fixture is missing.
     */
    private static bool $throwOnMissingFixtures = false;

    /**
     * Set the fixture path
     */
    public static function setFixturePath(string $path): void
    {
        self::$fixturePath = $path;
    }

    /**
     * Throw an exception if a fixture doesn't exist instead of recording it.
     */
    public static function throwOnMissingFixtures(): void
    {
        self::$throwOnMissingFixtures = true;
    }

    /**
     * Return the fixture path
     */
    public static function getFixturePath(): string
    {
        return self::$fixturePath;
    }

    /**
     * Should we throw an exception if a fixture is missing?
     */
    public static function isThrowingOnMissingFixtures(): bool
    {
        return self::$throwOnMissingFixtures;
    }
}
