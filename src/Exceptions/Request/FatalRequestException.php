<?php

declare(strict_types=1);

namespace Saloon\Exceptions\Request;

use Saloon\Contracts\PendingRequest;
use Saloon\Exceptions\SaloonException;
use Throwable;

class FatalRequestException extends SaloonException
{
    /**
     * The PendingRequest
     *
     * @var PendingRequest
     */
    protected PendingRequest $pendingSaloonRequest;

    /**
     * Constructor
     *
     * @param Throwable $originalException
     * @param PendingRequest $pendingRequest
     */
    public function __construct(Throwable $originalException, PendingRequest $pendingRequest)
    {
        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);

        $this->pendingSaloonRequest = $pendingRequest;
    }

    /**
     * Get the PendingRequest that caused the exception.
     *
     * @return PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingSaloonRequest;
    }
}
