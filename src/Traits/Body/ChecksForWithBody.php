<?php

namespace Saloon\Traits\Body;

use Saloon\Contracts\Body\WithBody;
use Saloon\Exceptions\BodyException;
use Saloon\Http\PendingRequest;

trait ChecksForWithBody
{
    /**
     * Check if the request or connector has the WithBody class.
     *
     * @param \Saloon\Http\PendingRequest $pendingRequest
     * @return void
     * @throws \Saloon\Exceptions\BodyException
     */
    public function bootChecksForWithBody(PendingRequest $pendingRequest): void
    {
        if ($pendingRequest->getRequest() instanceof WithBody || $pendingRequest->getConnector() instanceof WithBody) {
            return;
        }

        throw new BodyException('You have added a body trait without adding the `WithBody` interface to your request/connector.');
    }
}
