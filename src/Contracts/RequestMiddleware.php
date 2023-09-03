<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @return \Saloon\Contracts\PendingRequest|FakeResponse|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
