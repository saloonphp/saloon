<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\CustomBaseUrlConnector;

class CustomEndpointRequest extends Request
{
    /**
     * Connector
     *
     * @var string
     */
    protected string $connector = CustomBaseUrlConnector::class;

    /**
     * Endpoint
     *
     * @var string
     */
    protected string $endpoint = '';

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Set an endpoint
     *
     * @param string $endpoint
     * @return CustomEndpointRequest
     */
    public function setEndpoint(string $endpoint): CustomEndpointRequest
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
