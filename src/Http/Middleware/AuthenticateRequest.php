<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class AuthenticateRequest implements RequestMiddleware
{
    /**
     * Authenticate the pending request
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $authenticator = $pendingRequest->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $authenticator->set($pendingRequest);
        }
    }
}
