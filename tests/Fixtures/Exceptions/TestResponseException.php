<?php

namespace Saloon\Tests\Fixtures\Exceptions;

use Exception;
use Saloon\Contracts\PendingRequest;

class TestResponseException extends Exception
{
    /**
     * Pending Request
     *
     * @var \Saloon\Contracts\PendingRequest
     */
    protected PendingRequest $pendingRequest;

    /**
     * Constructor
     *
     * @param string $message
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function __construct(string $message, PendingRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;

        parent::__construct($message);
    }

    /**
     * Get the pending request
     *
     * @return \Saloon\Contracts\PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }
}
