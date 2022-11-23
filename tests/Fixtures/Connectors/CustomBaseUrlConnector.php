<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;

class CustomBaseUrlConnector extends Connector
{
    /**
     * Base URL
     *
     * @var string
     */
    protected string $baseUrl = '';

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Set a base URL
     *
     * @param string $baseUrl
     * @return CustomBaseUrlConnector
     */
    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }
}
