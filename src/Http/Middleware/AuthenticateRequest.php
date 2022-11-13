<?php declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingSaloonRequest;
use Saloon\Contracts\RequestMiddleware;

class AuthenticateRequest implements RequestMiddleware
{
    /**
     * Authenticate the pending request
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function __invoke(PendingSaloonRequest $pendingRequest): void
    {
        $authenticator = $pendingRequest->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $authenticator->set($pendingRequest);
        }
    }
}
