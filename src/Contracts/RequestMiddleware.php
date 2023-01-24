<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\PendingRequest|SimulatedResponsePayload|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
