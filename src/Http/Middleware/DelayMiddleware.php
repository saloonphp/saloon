<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Config;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class DelayMiddleware implements RequestMiddleware
{
    /**
     * Register a request middleware
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $delay = $pendingRequest->delay()->get() ?? 0;

        if ($delay > 0) {
            Config::sleep($delay * 1000);
        }
    }
}
