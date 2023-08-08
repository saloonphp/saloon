<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\CustomBaseUrlConnector;

class CustomEndpointRequest extends Request
{
    /**
     * Connector
     */
    protected string $connector = CustomBaseUrlConnector::class;

    /**
     * Endpoint
     */
    protected string $endpoint = '';

    /**
     * Method
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Set an endpoint
     */
    public function setEndpoint(string $endpoint): CustomEndpointRequest
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
