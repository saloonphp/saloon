<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

interface AuthenticatorInterface
{
    /**
     * Apply the authentication to the request.
     *
     * @param PendingSaloonRequest $requestPayload
     * @return void
     */
    public function set(PendingSaloonRequest $requestPayload): void;
}
