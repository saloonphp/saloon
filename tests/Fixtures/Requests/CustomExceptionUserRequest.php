<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Contracts\Response;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Exceptions\CustomRequestException;
use Throwable;

class CustomExceptionUserRequest extends Request
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
