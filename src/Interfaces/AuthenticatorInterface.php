<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\RequestPayload;

interface AuthenticatorInterface
{
    /**
     * Apply the authentication to the request.
     *
     * @param RequestPayload $requestPayload
     * @return void
     */
    public function set(RequestPayload $requestPayload): void;
}
