<?php

namespace Saloon\Contracts;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

/**
 * @internal
 */
interface HasRetry
{
    /**
     * Handle when the request is about to be retried.
     *
     * Return true/false to enable the retry or not.
     */
    public function handleRetry(Request $request, FatalRequestException|RequestException $exception, Response $response = null): bool;

    // Todo: Consider adding retry-until functionality
}
