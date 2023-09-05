<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface Authenticatable
{
    /**
     * Retrieve the authenticator.
     */
    public function getAuthenticator(): ?Authenticator;

    /**
     * Authenticate the request with an authenticator.
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static;

    /**
     * Authenticate the request with an Authorization header.
     *
     * @return $this
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static;

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static;

    /**
     * Authenticate the request with a query parameter token.
     *
     * @return $this
     */
    public function withQueryAuth(string $parameter, string $value): static;
}
