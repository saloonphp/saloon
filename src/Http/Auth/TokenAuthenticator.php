<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

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
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Authorization', trim($this->prefix . ' ' . $this->token));
    }
}
