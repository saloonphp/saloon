<?php

namespace Sammyjo20\Saloon\Contracts;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

interface Authenticator
{
    /**
     * Apply the authentication to the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function set(PendingSaloonRequest $pendingRequest): void;
}
