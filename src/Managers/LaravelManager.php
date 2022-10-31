<?php

namespace Sammyjo20\Saloon\Managers;

use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;

class LaravelManager
{
    use CollectsHeaders,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors;

    /**
     * Is the Laravel app in mocking mode?
     *
     * @var bool
     */
    protected bool $isMocking = false;

    /**
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * Set if we are mocking or not
     *
     * @param bool $isMocking
     * @return $this
     */
    public function setIsMocking(bool $isMocking): static
    {
        $this->isMocking = $isMocking;

        return $this;
    }

    /**
     * Is the Laravel app in mocking mode?
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->isMocking;
    }

    /**
     * Set the mock client on the manager.
     *
     * @param MockClient $mockClient
     * @return $this
     */
    public function setMockClient(MockClient $mockClient): static
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Return the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }
}
