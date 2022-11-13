<?php declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingSaloonRequest;

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
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function set(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->queryParameters()->add($this->parameter, $this->value);
    }
}
