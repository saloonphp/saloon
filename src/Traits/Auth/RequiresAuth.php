<?php

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresAuth
{
    /**
     * Throw an exception if an authenticator is not on the request while it is booting.
     *
     * @param SaloonRequest $request
     * @return void
     * @throws MissingAuthenticatorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootRequiresAuth(SaloonRequest $request): void
    {
        $connector = $request->getAuthenticator() ?? $request->getConnector()->getAuthenticator();

        if (! $connector instanceof AuthenticatorInterface) {
            throw new MissingAuthenticatorException($this->getRequiresAuthMessage());
        }
    }

    /**
     * Default message.
     *
     * @return string
     */
    protected function getRequiresAuthMessage(): string
    {
        return 'This request requires authentication. Please provide an authenticator using the `withAuth` method.';
    }
}
