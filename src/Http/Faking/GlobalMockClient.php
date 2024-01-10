<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

class GlobalMockClient
{
    /**
     * Global Mock Client
     *
     * The global mock client instance. If it has not been set it will be null.
     */
    protected static ?MockClient $mockClient = null;

    /**
     * Create a global mock client
     *
     * Make sure to destroy the global mock client after each test using `GlobalMockClient::destroy()`.
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $mockData
     */
    public static function make(array $mockData): MockClient
    {
        return static::$mockClient ??= new MockClient($mockData);
    }

    /**
     * Retrieve the global mock client from the store
     */
    public static function get(): ?MockClient
    {
        return static::$mockClient;
    }

    /**
     * Destroy the global mock client
     */
    public static function destroy(): void
    {
        static::$mockClient = null;
    }
}
