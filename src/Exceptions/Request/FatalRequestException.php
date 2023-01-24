<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Throwable;
use Saloon\Contracts\PendingRequest;
use Saloon\Exceptions\SaloonException;

class FatalRequestException extends SaloonException
{
    /**
     * The PendingRequest
     *
     * @var \Saloon\Contracts\PendingRequest
     */
    protected PendingRequest $pendingSaloonRequest;

    /**
     * Constructor
     *
     * @param \Throwable $originalException
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function __construct(Throwable $originalException, PendingRequest $pendingRequest)
    {
        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);

        $this->pendingSaloonRequest = $pendingRequest;
    }

    /**
     * Get the PendingRequest that caused the exception.
     *
     * @return \Saloon\Contracts\PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingSaloonRequest;
    }
}
