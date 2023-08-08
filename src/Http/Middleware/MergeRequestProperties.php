<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class MergeRequestProperties implements RequestMiddleware
{
    /**
     * Register a request middleware
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $pendingRequest->headers()->merge(
            $connector->headers()->all(),
            $request->headers()->all()
        );

        $pendingRequest->query()->merge(
            $connector->query()->all(),
            $request->query()->all()
        );

        $pendingRequest->config()->merge(
            $connector->config()->all(),
            $request->config()->all()
        );
    }
}
