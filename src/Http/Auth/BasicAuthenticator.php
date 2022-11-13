<?php declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingSaloonRequest;

class BasicAuthenticator implements Authenticator
{
    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        public string $username,
        public string $password,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function set(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('auth', [$this->username, $this->password]);
    }
}
