<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface Authenticator
{
    /**
     * Apply the authentication to the request.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function set(PendingRequest $pendingRequest): void;
}
