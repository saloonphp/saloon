<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http\Auth;

use Sammyjo20\Saloon\Contracts\Authenticator;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class DigestAuthenticator implements Authenticator
{
    /**
     * @param string $username
     * @param string $password
     * @param string $digest
     */
    public function __construct(
        public string $username,
        public string $password,
        public string $digest,
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
        $pendingRequest->config()->add('auth', [$this->username, $this->password, $this->digest]);
    }
}
