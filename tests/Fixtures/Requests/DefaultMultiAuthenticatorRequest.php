<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultMultiAuthenticatorRequest extends Request
{
    /**
     * Define the method that the request will use.
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Provide default authentication.
     */
    protected function defaultAuth(): array
    {
        return [
            new TokenAuthenticator('example'),
            new HeaderAuthenticator('api-key', 'X-API-Key'),
        ];
    }
}
