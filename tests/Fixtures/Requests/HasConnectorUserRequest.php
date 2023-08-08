<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Request\HasConnector;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class HasConnectorUserRequest extends Request
{
    use HasConnector;

    /**
     * Define connector
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the HTTP method.
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
