<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Http\SoloRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Traits\Request\HasConnector;

class HasConnectorUserRequest extends Request
{
    use HasConnector;

    /**
     * Define connector
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the HTTP method.
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
