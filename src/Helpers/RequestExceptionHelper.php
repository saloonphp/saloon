<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Throwable;
use Saloon\Contracts\Response;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\InternalServerErrorException;

class RequestExceptionHelper
{
    /**
     * Create the request exception from a response
     *
     * @param \Saloon\Contracts\Response $response
     * @param \Throwable|null $previous
     * @return \Saloon\Exceptions\Request\RequestException
     */
    public static function create(Response $response, Throwable $previous = null): RequestException
    {
        $status = $response->status();

        $requestException = match (true) {
            // Built-in exceptions
            $status === 500 => InternalServerErrorException::class,

            // Fall-back exceptions
            $response->serverError() => ServerException::class,
            $response->clientError() => ClientException::class,
            default => RequestException::class,
        };

        return new $requestException($response, null, 0, $previous);
    }
}
