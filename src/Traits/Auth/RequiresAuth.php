<?php

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresAuth
{
    /**
     * Throw an exception if an authenticator is not on the request while it is booting.
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     * @return void
     * @throws MissingAuthenticatorException
     */
    public function bootRequiresAuth(PendingSaloonRequest $pendingSaloonRequest): void
    {
        $authenticator = $pendingSaloonRequest->getAuthenticator();

        if (! $authenticator instanceof AuthenticatorInterface) {
            throw new MissingAuthenticatorException($this->getRequiresAuthMessage($pendingSaloonRequest));
        }
    }

    /**
     * Default message.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingSaloonRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide an authenticator using the `withAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
