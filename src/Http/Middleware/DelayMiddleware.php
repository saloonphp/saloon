<?php

namespace Saloon\Http\Middleware;

use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class DelayMiddleware implements RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $delay = $pendingRequest->delay()->get() ?? 0;

        usleep($delay * 1000);
    }
}
