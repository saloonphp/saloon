<?php

declare(strict_types=1);

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;

class MergeRequestProperties
{
    /**
     * Merge connector and request properties (headers, query, config, middleware)
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
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

        $pendingRequest->middleware()
            ->merge($connector->middleware())
            ->merge($request->middleware());

        return $pendingRequest;
    }
}
