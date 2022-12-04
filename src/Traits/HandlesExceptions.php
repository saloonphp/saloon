<?php

namespace Saloon\Traits;

use Saloon\Contracts\Response;
use Throwable;

trait HandlesExceptions
{
    /**
     * Determine if the request has failed
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool
     */
    public function shouldThrowRequestException(Response $response): bool
    {
        return $response->serverError() || $response->clientError();
    }

    /**
     * Handle the request exception.
     *
     * @param \Saloon\Contracts\Response $response
     * @param \Throwable|null $senderException
     * @return \Throwable|null
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return null;
    }
}
