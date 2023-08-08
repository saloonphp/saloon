<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;

class CustomBaseUrlConnector extends Connector
{
    /**
     * Base URL
     */
    protected string $baseUrl = '';

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Set a base URL
     *
     * @return CustomBaseUrlConnector
     */
    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }
}
