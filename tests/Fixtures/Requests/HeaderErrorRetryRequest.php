<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;

class HeaderErrorRetryRequest extends RetryUserRequest
{
    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/header-error';
    }
}
