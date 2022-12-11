<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Throwable;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Response;
use Saloon\Tests\Fixtures\Exceptions\CustomRequestException;

class CustomExceptionUserRequest extends Request
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
     * Get the custom request exception
     *
     * @param \Saloon\Contracts\Response $response
     * @param \Throwable|null $senderException
     * @return \Throwable|null
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return new CustomRequestException($response, 'Oh yee-naw.', 0, $senderException);
    }
}
