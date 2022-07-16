<?php

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresBasicAuth
{
    use RequiresAuth;

    /**
     * @throws MissingAuthenticatorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootRequiresBasicAuth(PendingSaloonRequest $pendingRequest): void
    {
        $this->bootRequiresAuth($pendingRequest);
    }

    /**
     * Default message.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingSaloonRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide authentication using the `withBasicAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
