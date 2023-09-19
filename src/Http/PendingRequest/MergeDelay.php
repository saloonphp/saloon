<?php

declare(strict_types=1);

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;

class MergeDelay
{
    /**
     * Merge connector and request delay
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $pendingRequest->delay()->set($connector->delay()->get());

        if ($request->delay()->isNotEmpty()) {
            $pendingRequest->delay()->set($request->delay()->get());
        }

        return $pendingRequest;
    }
}
