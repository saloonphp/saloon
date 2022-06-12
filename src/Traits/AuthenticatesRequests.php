<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\Auth\BasicAuthenticator;
use Sammyjo20\Saloon\Http\Auth\TokenAuthenticator;
use Sammyjo20\Saloon\Http\Auth\DigestAuthenticator;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;

trait AuthenticatesRequests
{
    /**
     * The authenticator used in requests.
     *
     * @var AuthenticatorInterface|null
     */
    public ?AuthenticatorInterface $authenticator = null;

    /**
     * Default authenticator used.
     *
     * @return AuthenticatorInterface|null
     */
    public function defaultAuth(): ?AuthenticatorInterface
    {
        return null;
    }

    /**
     * Retrieve the authenticator.
     *
     * @return AuthenticatorInterface|null
     */
    public function getAuthenticator(): ?AuthenticatorInterface
    {
        return $this->authenticator ?? $this->defaultAuth();
    }

    /**
     * Register an authenticator
     *
     * @param AuthenticatorInterface $authenticator
     * @return $this
     */
    public function withAuth(AuthenticatorInterface $authenticator): static
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * Register an authenticator
     *
     * @param AuthenticatorInterface $authenticator
     * @return $this
     */
    public function authenticate(AuthenticatorInterface $authenticator): static
    {
        return $this->withAuth($authenticator);
    }

    /**
     * Attach an Authorization token to the request.
     *
     * @param string $token
     * @param string $prefix
     * @return $this
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static
    {
        return $this->withAuth(new TokenAuthenticator($token, $prefix));
    }

    /**
     * Attach basic authentication to the request.
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static
    {
        return $this->withAuth(new BasicAuthenticator($username, $password));
    }

    /**
     * Attach basic authentication to the request.
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     * @return $this
     */
    public function withDigestAuth(string $username, string $password, string $digest): static
    {
        return $this->withAuth(new DigestAuthenticator($username, $password, $digest));
    }
}
