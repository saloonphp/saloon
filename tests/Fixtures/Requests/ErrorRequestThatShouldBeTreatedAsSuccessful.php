<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ErrorRequestThatShouldBeTreatedAsSuccessful extends Request
{
    /**
     * HTTP Method
     */
    protected Method $method = Method::GET;

    /**
     * Resolve the endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/not-found-error';
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== 404;
    }
}
