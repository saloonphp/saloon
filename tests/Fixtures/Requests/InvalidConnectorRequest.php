<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class InvalidConnectorRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = 'abc';

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
