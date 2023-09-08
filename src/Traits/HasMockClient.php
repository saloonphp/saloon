<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\Faking\MockClient;

trait HasMockClient
{
    /**
     * Mock Client
     */
    protected ?MockClient $mockClient = null;

    /**
     * Specify a mock client.
     *
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Get the mock client.
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }

    /**
     * Determine if the instance has a mock client
     */
    public function hasMockClient(): bool
    {
        return $this->mockClient instanceof MockClient;
    }
}
