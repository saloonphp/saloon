<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class QueryAuthenticator implements Authenticator
{
    /**
     * Constructor
     *
     * @param string $parameter
     * @param string $value
     */
    public function __construct(
        public string $parameter,
        public string $value,
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
        $pendingRequest->query()->add($this->parameter, $this->value);
    }
}
