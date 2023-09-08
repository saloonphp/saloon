<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\PendingRequest;

interface Authenticator
{
    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void;
}
