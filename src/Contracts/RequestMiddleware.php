<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\PendingRequest;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @return \Saloon\Http\PendingRequest|FakeResponse|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
