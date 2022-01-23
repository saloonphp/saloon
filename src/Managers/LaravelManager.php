<?php

namespace Sammyjo20\Saloon\Managers;

use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Clients\BaseMockClient;
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
     * @var BaseMockClient|null
     */
    protected ?BaseMockClient $mockClient = null;

    /**
     * Set if we are mocking or not
     *
     * @param bool $isMocking
     * @return $this
     */
    public function setIsMocking(bool $isMocking): self
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
     * @param BaseMockClient $mockClient
     * @return $this
     */
    public function setMockClient(BaseMockClient $mockClient): self
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Return the mock client.
     *
     * @return BaseMockClient|null
     */
    public function getMockClient(): ?BaseMockClient
    {
        return $this->mockClient;
    }
}
