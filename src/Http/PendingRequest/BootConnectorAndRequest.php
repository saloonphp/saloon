<?php

declare(strict_types=1);

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;

class BootConnectorAndRequest
{
    /**
     * Boot the connector and request
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
    {
        $pendingRequest->getConnector()->boot($pendingRequest);
        $pendingRequest->getRequest()->boot($pendingRequest);

        return $pendingRequest;
    }
}
