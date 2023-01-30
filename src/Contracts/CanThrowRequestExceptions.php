<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Throwable;

interface CanThrowRequestExceptions
{
    /**
     * Determine if the request has failed.
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool|null
     */
    public function hasRequestFailed(Response $response): ?bool;

    /**
     * Determine if we should throw an exception if the `$response->throw()` ({@see \Saloon\Contracts\Response::throw()})
     * is used, or when AlwaysThrowOnErrors is used.
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool
     */
    public function shouldThrowRequestException(Response $response): bool;

    /**
     * Get the request exception.
     *
     * @param \Saloon\Contracts\Response $response
     * @param \Throwable|null $senderException
     * @return \Throwable|null
     */
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable;
}
