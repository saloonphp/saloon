<?php

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresTokenAuth
{
    use RequiresAuth;

    /**
     * @throws MissingAuthenticatorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootRequiresTokenAuth(SaloonRequest $request): void
    {
        $this->bootRequiresAuth($request);
    }

    /**
     * Exception message.
     *
     * @return string
     */
    protected function getRequiresAuthMessage(): string
    {
        return 'This request requires token authentication. Please provide authentication using the `withTokenAuth` method or return a default authenticator in your connector/request.';
    }
}
