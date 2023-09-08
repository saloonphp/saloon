<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class MergeDelay implements RequestMiddleware
{
    /**
     * Register a request middleware
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $pendingRequest->delay()->set($connector->delay()->get());

        if ($request->delay()->isNotEmpty()) {
            $pendingRequest->delay()->set($request->delay()->get());
        }
    }
}
