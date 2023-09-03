<?php

namespace Saloon\Traits;

use Closure;
use Saloon\Contracts\Request as RequestContract;
use Saloon\Contracts\Response;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

trait HasTries
{
    /**
     * The number of tries a request will be attempted
     *
     * Null to disable the retry functionality.
     *
     * @var int|null
     */
    public ?int $tries = null;

    /**
     * The interval between attempting to retry a request
     *
     * @var int
     */
    public int $retryInterval = 0;

    /**
     * Should Saloon throw an exception on max tries?
     *
     * Set to null to use default behaviour (throw)
     *
     * @var bool|null
     */
    public ?bool $throwOnMaxTries = null;

    /**
     * Handle the retry from a callable
     *
     * Only used by the sendAndRetry method. Use the `handleRetry` method if you are in the context
     * of a connector or request.
     *
     * @var \Closure|null
     */
    public ?Closure $handleRetryCallable = null;

    /**
     * Handle when the request is about to be retried.
     */
    public function handleRetry(RequestContract $request, FatalRequestException|RequestException $exception, Response $response = null): bool
    {
        return true;
    }
}
