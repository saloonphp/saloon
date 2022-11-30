<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class UserRequest extends Request
{
    /**
     * Resolve the method for the request
     *
     * @return string
     */
    public function resolveMethod(): string
    {
        return 'GET';
    }

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
