<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Exceptions;

use Exception;
use Saloon\Http\PendingRequest;

class TestResponseException extends Exception
{
    /**
     * Pending Request
     */
    protected PendingRequest $pendingRequest;

    /**
     * Constructor
     */
    public function __construct(string $message, PendingRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;

        parent::__construct($message);
    }

    /**
     * Get the pending request
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }
}
