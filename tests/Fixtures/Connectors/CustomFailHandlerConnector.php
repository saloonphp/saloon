<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class CustomFailHandlerConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Determine if the request has failed
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return str_contains($response->body(), 'Error:');
    }
}
