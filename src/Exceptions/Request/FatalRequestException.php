<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Throwable;
use Saloon\Http\PendingRequest;
use Saloon\Exceptions\SaloonException;

/**
 * FatalRequestException
 *
 * This exception is thrown when the sender encountered a problem before the API
 * was able to respond. For example: An issue with connecting to the API or
 * an SSL error.
 *
 * @see https://docs.saloon.dev/the-basics/handling-failures
 */
class FatalRequestException extends SaloonException
{
    /**
     * The PendingRequest
     */
    protected PendingRequest $pendingSaloonRequest;

    /**
     * Constructor
     */
    public function __construct(Throwable $originalException, PendingRequest $pendingRequest)
    {
        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);

        $this->pendingSaloonRequest = $pendingRequest;
    }

    /**
     * Get the PendingRequest that caused the exception.
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingSaloonRequest;
    }
}
