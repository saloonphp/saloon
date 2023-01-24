<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasMockClient
{
    /**
     * Specify a mock client.
     *
     * @param \Saloon\Contracts\MockClient $mockClient
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static;

    /**
     * Get the mock client.
     *
     * @return \Saloon\Contracts\MockClient|null
     */
    public function getMockClient(): ?MockClient;

    /**
     * Determine if the instance has a mock client
     *
     * @return bool
     */
    public function hasMockClient(): bool;
}
