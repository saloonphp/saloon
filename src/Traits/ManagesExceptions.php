<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Throwable;
use Saloon\Http\Response;

trait ManagesExceptions
{
    /**
     * Determine if the request has failed.
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return null;
    }

    /**
     * Determine if we should throw an exception if the `$response->throw()` ({@see \Saloon\Http\Response::throw()})
     * is used, or when AlwaysThrowOnErrors is used.
     */
    public function shouldThrowRequestException(Response $response): bool
    {
        return $response->failed();
    }

    /**
     * Get the request exception.
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return null;
    }
}
