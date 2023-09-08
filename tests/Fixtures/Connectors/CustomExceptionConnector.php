<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Throwable;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Exceptions\ConnectorRequestException;

class CustomExceptionConnector extends Connector
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
     * Customise the request exception handler
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return new ConnectorRequestException($response, 'Oh yee-naw.', 0, $senderException);
    }
}
