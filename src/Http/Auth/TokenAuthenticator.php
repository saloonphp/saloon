<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

class TokenAuthenticator implements Authenticator
{

    public function __construct(
        public string $token,
        public string $prefix = 'Bearer'
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Authorization', trim($this->prefix . ' ' . $this->token));
    }
}
