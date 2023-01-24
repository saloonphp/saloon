<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class AuthenticateRequest implements RequestMiddleware
{
    /**
     * Authenticate the pending request
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $authenticator = $pendingRequest->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $authenticator->set($pendingRequest);
        }
    }
}
