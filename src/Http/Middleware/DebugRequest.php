<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Debugging\DebugData;
use Saloon\Contracts\HasDebugging;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class DebugRequest implements RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $connector = $pendingRequest->getConnector();

        if (! $connector instanceof HasDebugging) {
            return;
        }

        $connector->debug()->send(new DebugData($pendingRequest, null));
    }
}
