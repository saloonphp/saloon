<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Response;

class CustomFailHandlerRequest extends Request
{
    /**
     * Define the HTTP method.
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Determine if the request has failed
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool
     */
    public function hasRequestFailed(Response $response): bool
    {
        return str_contains($response->body(), 'Yee-naw:');
    }
}
