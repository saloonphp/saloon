<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Http\SoloRequest;

class SoloUserRequest extends SoloRequest
{
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
        return 'https://tests.saloon.dev/api/user';
    }
}
