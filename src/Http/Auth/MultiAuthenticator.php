<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class MultiAuthenticator implements Authenticator
{
    /**
     * Authenticators
     *
     * @var array<\Saloon\Contracts\Authenticator>
     */
    protected readonly array $authenticators;

    /**
     * Constructor
     */
    public function __construct(Authenticator ...$authenticators)
    {
        $this->authenticators = $authenticators;
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
