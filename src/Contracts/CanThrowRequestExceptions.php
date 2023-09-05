<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Throwable;

interface CanThrowRequestExceptions
{
    /**
     * Determine if the request has failed.
     */
    public function hasRequestFailed(Response $response): ?bool;

    /**
     * Determine if we should throw an exception if the `$response->throw()` ({@see \Saloon\Contracts\Response::throw()})
     * is used, or when AlwaysThrowOnErrors is used.
     */
    public function shouldThrowRequestException(Response $response): bool;

    /**
     * Get the request exception.
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable;
}
