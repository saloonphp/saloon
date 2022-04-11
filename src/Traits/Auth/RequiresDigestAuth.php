<?php

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;
use Sammyjo20\Saloon\Http\SaloonRequest;

trait RequiresDigestAuth
{
    use RequiresAuth;

    /**
     * @throws MissingAuthenticatorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootRequiresDigestAuth(SaloonRequest $request): void
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
        return 'This request requires digest authentication. Please provide authentication using the `withDigestAuth` method.';
    }
}
