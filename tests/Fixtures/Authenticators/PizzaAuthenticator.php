<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Authenticators;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class PizzaAuthenticator implements Authenticator
{

    public function __construct(
        public string $pizza,
        public string $drink,
    ) {
        //
    }

    /**
     * Set the pending request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('X-Pizza', $this->pizza);
        $pendingRequest->headers()->add('X-Drink', $this->drink);

        $pendingRequest->config()->add('debug', true);
    }
}
