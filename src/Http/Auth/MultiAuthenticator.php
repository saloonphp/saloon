<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class MultiAuthenticator implements Authenticator
{
    /**
     * Constructor
     *
     * @param array<\Saloon\Contracts\Authenticator> $authenticators
     */
    public function __construct(protected readonly array $authenticators)
    {
        //
    }

    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        foreach ($this->authenticators as $authenticator) {
            $authenticator->set($pendingRequest);
        }
    }
}
