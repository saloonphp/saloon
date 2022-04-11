<?php

namespace Sammyjo20\Saloon\Http\Auth;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;

class DigestAuthenticator implements AuthenticatorInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param string $digest
     */
    public function __construct(
        public string $username,
        public string $password,
        public string $digest,
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
        $request->addConfig('auth', [$this->username, $this->password, $this->digest]);
    }
}
