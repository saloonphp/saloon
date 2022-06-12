<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Clients\MockClient;

trait MocksRequests
{
    /**
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * Register a mock client for the request.
     *
     * @param MockClient $mockClient
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Get the mock client on the request.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }
}
