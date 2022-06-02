<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

interface AuthenticatorInterface
{
    /**
     * Apply the authentication to the request.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function set(PendingSaloonRequest $request): void;
}
