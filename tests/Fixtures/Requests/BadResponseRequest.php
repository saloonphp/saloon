<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class BadResponseRequest extends Request
{
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

    /**
     * Check if we should throw an exception
     */
    public function shouldThrowRequestException(Response $response): bool
    {
        return str_contains($response->body(), 'Yee-naw:');
    }
}
