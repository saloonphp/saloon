<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

/**
 * @mixin \Saloon\Http\Faking\MockClient
 */
class GlobalMockClient
{
    /**
     * The instance of global mock client
     */
    protected static ?self $instance = null;

    /**
     * Mock Client
     *
     * The global mock client instance. When this is null, no global mock client will be used.
     */
    protected MockClient $mockClient;

    /**
     * Constructor
     *
     * Note: You should destroy the global mock client after each test using `GlobalMockClient::destroy()`.
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $mockData
     */
    public function __construct(array $mockData)
    {
        $this->mockClient = new MockClient($mockData);

        static::$instance = $this;
    }

    /**
     * Create a global mock client
     *
     * Note: You should destroy the global mock client after each test using `GlobalMockClient::destroy()`.
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $mockData
     */
    public static function make(array $mockData): self
    {
        return new static($mockData);
    }

    /**
     * Get the underlying MockClient from the GlobalMockClient
     */
    public function getMockClient(): MockClient
    {
        return $this->mockClient;
    }

    /**
     * Resolve the global mock client
     */
    public static function resolve(): ?self
    {
        return static::$instance;
    }

    /**
     * Destroy the global mock client
     */
    public static function destroy(): void
    {
        static::$instance = null;
    }

    /**
     * Proxy method calls to the MockClient
     *
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->mockClient->$name(...$arguments);
    }
}
