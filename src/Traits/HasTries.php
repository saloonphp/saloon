<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Contracts\Request;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

trait HasTries
{
    /**
     * The number of tries a request will be attempted
     *
     * Set to null to disable the retry functionality.
     */
    public ?int $tries = null;

    /**
     * The interval between attempting to retry a request
     *
     * Set to null to disable the interval.
     */
    public ?int $retryInterval = null;

    /**
     * Should Saloon throw an exception on max tries?
     *
     * Set to null to always throw after maximum tries.
     */
    public ?bool $throwOnMaxTries = null;

    /**
     * Handle when the request is about to be retried.
     */
    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        return true;
    }
}
