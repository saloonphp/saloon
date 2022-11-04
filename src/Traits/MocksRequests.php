<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Contracts\MockClient;

trait MocksRequests
{
    /**
     * Mock Client
     *
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * Specify a mock client.
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
     * Get the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }
}
