<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Authenticators;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class PizzaAuthenticator implements Authenticator
{
    /**
     * @param string $pizza
     * @param string $drink
     */
    public function __construct(
        public string $pizza,
        public string $drink,
    ) {
        //
    }

    /**
     * Set the pending request.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('X-Pizza', $this->pizza);
        $pendingRequest->headers()->add('X-Drink', $this->drink);

        $pendingRequest->config()->add('debug', true);
    }
}
