<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Response;
use Saloon\Traits\Plugins\AcceptsJson;

class CustomFailHandlerConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Determine if the request has failed
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool|null
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return str_contains($response->body(), 'Error:');
    }
}

