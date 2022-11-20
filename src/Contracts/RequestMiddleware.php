<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Faking\SimulatedResponsePayload;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @param PendingRequest $pendingRequest
     * @return PendingRequest|SimulatedResponsePayload|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
