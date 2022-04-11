<?php

namespace Sammyjo20\Saloon\Http\Auth;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;

class BasicAuthenticator implements AuthenticatorInterface
{
    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        public string $username,
        public string $password,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function set(SaloonRequest $request): void
    {
        $request->addConfig('auth', [$this->username, $this->password]);
    }
}
