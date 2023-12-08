<?php

declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\QueryAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Auth\DigestAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Auth\CertificateAuthenticator;

trait AuthenticatesRequests
{
    /**
     * The authenticator used in requests.
     */
    protected ?Authenticator $authenticator = null;

    /**
     * Default authenticator used.
     *
     * @return \Saloon\Contracts\Authenticator|array<\Saloon\Contracts\Authenticator>|null
     */
    protected function defaultAuth(): Authenticator|array|null
    {
        return null;
    }

    /**
     * Retrieve the authenticator.
     */
    public function getAuthenticator(): ?Authenticator
    {
        if (isset($this->authenticator)) {
            return $this->authenticator;
        }

        if ($defaultAuth = $this->defaultAuth()) {
            $this->authenticate($defaultAuth);
        }

        return $this->authenticator;
    }

    /**
     * Authenticate the request with an authenticator.
     *
     * @param \Saloon\Contracts\Authenticator|array<\Saloon\Contracts\Authenticator> $authenticator
     * @return $this
     */
    public function authenticate(Authenticator|array $authenticator): static
    {
        $this->authenticator = is_array($authenticator) ? new MultiAuthenticator($authenticator) :$authenticator;

        return $this;
    }

    /**
     * Authenticate the request with an Authorization header.
     *
     * @return $this
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static
    {
        return $this->authenticate(new TokenAuthenticator($token, $prefix));
    }

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static
    {
        return $this->authenticate(new BasicAuthenticator($username, $password));
    }

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @return $this
     */
    public function withDigestAuth(string $username, string $password, string $digest): static
    {
        return $this->authenticate(new DigestAuthenticator($username, $password, $digest));
    }

    /**
     * Authenticate the request with a query parameter token.
     *
     * @return $this
     */
    public function withQueryAuth(string $parameter, string $value): static
    {
        return $this->authenticate(new QueryAuthenticator($parameter, $value));
    }

    /**
     * Authenticate the request with a header.
     *
     * @return $this
     */
    public function withHeaderAuth(string $accessToken, string $headerName = 'Authorization'): static
    {
        return $this->authenticate(new HeaderAuthenticator($accessToken, $headerName));
    }

    /**
     * Authenticate the request with a certificate.
     *
     * @return $this
     */
    public function withCertificateAuth(string $path, ?string $password = null): static
    {
        return $this->authenticate(new CertificateAuthenticator($path, $password));
    }
}
