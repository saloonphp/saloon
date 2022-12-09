<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Contracts\Response;

class BadResponseRequest extends Request
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
        return '/user';
    }

    /**
     * Check if we should throw an exception
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool
     */
    public function shouldThrowRequestException(Response $response): bool
    {
        return str_contains($response->body(), 'Yee-naw:');
    }
}
