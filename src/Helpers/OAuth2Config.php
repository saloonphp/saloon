<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\MockResponse;

class OAuth2Config
{
    /**
     * The Client ID
     *
     * @var string
     */
    protected string $clientId = '';

    /**
     * The Client Secret
     *
     * @var string
     */
    protected string $clientSecret = '';

    /**
     * The Redirect URI
     *
     * @var string
     */
    protected string $redirectUri = '';

    /**
     * The endpoint used to retrieve credentials.
     *
     * @var string
     */
    protected string $authorizeEndpoint = 'authorize';

    /**
     * The endpoint used to refresh credentials.
     *
     * @var string
     */
    protected string $tokenEndpoint = 'token';

    /**
     * The endpoint used to retrieve user information.s
     *
     * @var string
     */
    protected string $userEndpoint = 'user';

    /**
     * Create a new instance of the class.
     *
     * @return static
     */
    public static function make(): self
    {
        return new static;
    }

    /**
     * Get the Client ID
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Set the Client ID
     *
     * @param string $clientId
     * @return OAuth2Config
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get the Client Secret
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Set the Client Secret
     *
     * @param string $clientSecret
     * @return OAuth2Config
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get the Redirect URI
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * Set the Redirect URI
     *
     * @param string $redirectUri
     * @return OAuth2Config
     */
    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get the authorization endpoint.
     *
     * @return string
     */
    public function getAuthorizeEndpoint(): string
    {
        return $this->authorizeEndpoint;
    }

    /**
     * Set the authorization endpoint.
     *
     * @param string $authorizeEndpoint
     * @return OAuth2Config
     */
    public function setAuthorizeEndpoint(string $authorizeEndpoint): self
    {
        $this->authorizeEndpoint = $authorizeEndpoint;

        return $this;
    }

    /**
     * Get the token endpoint.
     *
     * @return string
     */
    public function getTokenEndpoint(): string
    {
        return $this->tokenEndpoint;
    }

    /**
     * Set the token endpoint.
     *
     * @param string $tokenEndpoint
     * @return OAuth2Config
     */
    public function setTokenEndpoint(string $tokenEndpoint): self
    {
        $this->tokenEndpoint = $tokenEndpoint;

        return $this;
    }

    /**
     * Get the user endpoint.
     *
     * @return string
     */
    public function getUserEndpoint(): string
    {
        return $this->userEndpoint;
    }

    /**
     * Set the user endpoint.
     *
     * @param string $userEndpoint
     * @return OAuth2Config
     */
    public function setUserEndpoint(string $userEndpoint): self
    {
        $this->userEndpoint = $userEndpoint;

        return $this;
    }
}
