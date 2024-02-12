<?php

declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
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
     */
    protected function defaultAuth(): ?Authenticator
    {
        return null;
    }

    /**
     * Retrieve the authenticator.
     */
    public function getAuthenticator(): ?Authenticator
    {
        return $this->authenticator ?? $this->defaultAuth();
    }

    /**
     * Authenticate the request with an authenticator.
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * Authenticate the request with an Authorization header.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new TokenAuthenticator)` instead.
     * @return $this
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static
    {
        return $this->authenticate(new TokenAuthenticator($token, $prefix));
    }

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new BasicAuthenticator)` instead.
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static
    {
        return $this->authenticate(new BasicAuthenticator($username, $password));
    }

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new DigestAuthenticator)` instead.
     * @return $this
     */
    public function withDigestAuth(string $username, string $password, string $digest): static
    {
        return $this->authenticate(new DigestAuthenticator($username, $password, $digest));
    }

    /**
     * Authenticate the request with a query parameter token.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new QueryAuthenticator)` instead.
     * @return $this
     */
    public function withQueryAuth(string $parameter, string $value): static
    {
        return $this->authenticate(new QueryAuthenticator($parameter, $value));
    }

    /**
     * Authenticate the request with a header.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new HeaderAuthenticator)` instead.
     * @return $this
     */
    public function withHeaderAuth(string $accessToken, string $headerName = 'Authorization'): static
    {
        return $this->authenticate(new HeaderAuthenticator($accessToken, $headerName));
    }

    /**
     * Authenticate the request with a certificate.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new CertificateAuthenticator)` instead.
     * @return $this
     */
    public function withCertificateAuth(string $path, ?string $password = null): static
    {
        return $this->authenticate(new CertificateAuthenticator($path, $password));
    }
}
