<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

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
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool;
}
