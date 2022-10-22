<?php

namespace Sammyjo20\Saloon\Helpers;

class MockConfig
{
    /**
     * Default fixture path
     *
     * @var string
     */
    private static string $fixturePath = 'tests/Fixtures/Saloon';

    /**
     * Set the fixture path
     *
     * @param string $path
     * @return void
     */
    public static function setFixturePath(string $path): void
    {
        self::$fixturePath = $path;
    }

    /**
     * Return the fixture path
     *
     * @return string
     */
    public static function getFixturePath(): string
    {
        return self::$fixturePath;
    }
}
