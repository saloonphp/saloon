<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasMockClient
{
    /**
     * Specify a mock client.
     *
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static;

    /**
     * Get the mock client.
     */
    public function getMockClient(): ?MockClient;

    /**
     * Determine if the instance has a mock client
     */
    public function hasMockClient(): bool;
}
