<?php

namespace Sammyjo20\Saloon\Http\Auth;

use Sammyjo20\Saloon\Contracts\Authenticator;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class TokenAuthenticator implements Authenticator
{
    /**
     * @param string $token
     * @param string $prefix
     */
    public function __construct(
        public string $token,
        public string $prefix = 'Bearer'
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function set(PendingSaloonRequest $request): void
    {
        $request->headers()->add('Authorization', trim($this->prefix . ' ' . $this->token));
    }
}
