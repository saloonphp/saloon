<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Contracts\Authenticator;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class AuthenticateRequest
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
