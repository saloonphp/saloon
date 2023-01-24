<?php

declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;
use Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresAuth
{
    /**
     * Throw an exception if an authenticator is not on the request while it is booting.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingSaloonRequest
     * @return void
     * @throws \Saloon\Exceptions\MissingAuthenticatorException
     */
    public function bootRequiresAuth(PendingRequest $pendingSaloonRequest): void
    {
        $authenticator = $pendingSaloonRequest->getAuthenticator();

        if (! $authenticator instanceof Authenticator) {
            throw new MissingAuthenticatorException($this->getRequiresAuthMessage($pendingSaloonRequest));
        }
    }

    /**
     * Default message.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication.', $pendingRequest->getRequest()::class);
    }
}
