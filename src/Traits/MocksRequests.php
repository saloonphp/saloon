<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Clients\MockClient;

trait MocksRequests
{
    /**
     * @var MockClient
     */
    protected ?MockClient $mockClient = null;

    /**
     * Register a mock client for the request.
     *
     * @param MockClient $mockClient
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): self
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * @return MockClient
     */
    public function getMockClient(): MockClient
    {
        return $this->mockClient;
    }
}
