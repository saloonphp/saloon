<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Throwable;
use Saloon\Http\Response;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeoutException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;
use Saloon\Exceptions\Request\Statuses\PaymentRequiredException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\MethodNotAllowedException;
use Saloon\Exceptions\Request\Statuses\ServiceUnavailableException;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;

class RequestExceptionHelper
{
    /**
     * Create the request exception from a response
     */
    public static function create(Response $response, ?Throwable $previous = null): RequestException
    {
        $status = $response->status();

        $requestException = match (true) {
            // Built-in exceptions
            $status === 401 => UnauthorizedException::class,
            $status === 402 => PaymentRequiredException::class,
            $status === 403 => ForbiddenException::class,
            $status === 404 => NotFoundException::class,
            $status === 405 => MethodNotAllowedException::class,
            $status === 408 => RequestTimeOutException::class,
            $status === 422 => UnprocessableEntityException::class,
            $status === 429 => TooManyRequestsException::class,
            $status === 500 => InternalServerErrorException::class,
            $status === 503 => ServiceUnavailableException::class,
            $status === 504 => GatewayTimeoutException::class,

            // Fall-back exceptions
            $response->serverError() => ServerException::class,
            $response->clientError() => ClientException::class,
            default => RequestException::class,
        };

        return new $requestException($response, null, 0, $previous);
    }
}
