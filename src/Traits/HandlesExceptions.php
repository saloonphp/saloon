<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Throwable;
use Saloon\Contracts\Response;

trait HandlesExceptions
{
    /**
     * Determine if the request has failed.
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool|null
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return null;
    }

    /**
     * Determine if the request has failed
     *
     * @param \Saloon\Contracts\Response $response
     * @return bool
     */
    public function shouldThrowRequestException(Response $response): bool
    {
        return $response->failed();
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
