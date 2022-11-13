<?php declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\PendingSaloonRequest;

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
