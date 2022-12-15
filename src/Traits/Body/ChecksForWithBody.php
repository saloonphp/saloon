<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Body\WithBody;
use Saloon\Exceptions\BodyException;

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

        throw new BodyException(sprintf('You have added a body trait without adding the `%s` interface to your request/connector.', WithBody::class));
    }
}
