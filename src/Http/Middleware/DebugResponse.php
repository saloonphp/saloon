<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Response;
use Saloon\Debugging\DebugData;
use Saloon\Contracts\HasDebugging;
use Saloon\Contracts\ResponseMiddleware;

class DebugResponse implements ResponseMiddleware
{
    /**
     * Register a response middleware
     *
     * @param \Saloon\Contracts\Response $response
     * @return void
     */
    public function __invoke(Response $response): void
    {
        $pendingRequest = $response->getPendingRequest();
        $connector = $pendingRequest->getConnector();

        if (! $connector instanceof HasDebugging) {
            return;
        }

        $connector->debug()->send(new DebugData($pendingRequest, $response));
    }
}
