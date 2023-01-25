<?php

declare(strict_types=1);

namespace Saloon\Helpers\OAuth2;

use Saloon\Traits\Makeable;
use Saloon\Contracts\Makeable as MakeableContract;
use Saloon\Exceptions\OAuthConfigValidationException;

/**
 * @method static static make()
 */
class OAuthConfig implements MakeableContract
{
    use Makeable;

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
     * The endpoint used for the authorization URL.
     *
     * @var string
     */
    protected string $authorizeEndpoint = 'authorize';

    /**
     * The endpoint used to create and refresh tokens.
     *
     * @var string
     */
    protected string $tokenEndpoint = 'token';

    /**
     * The endpoint used to retrieve user information.
     *
     * @var string
     */
    protected string $userEndpoint = 'user';

    /**
     * The default scopes that will be applied to every authorization URL.
     *
     * @var array<string>
     */
    protected array $defaultScopes = [];

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
     * @return $this
     */
    public function setClientId(string $clientId): static
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
     * @return $this
     */
    public function setClientSecret(string $clientSecret): static
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
     * @return $this
     */
    public function setRedirectUri(string $redirectUri): static
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
     * @return $this
     */
    public function setAuthorizeEndpoint(string $authorizeEndpoint): static
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
     * @return $this
     */
    public function setTokenEndpoint(string $tokenEndpoint): static
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
     * @return $this
     */
    public function setUserEndpoint(string $userEndpoint): static
    {
        $this->userEndpoint = $userEndpoint;

        return $this;
    }

    /**
     * Get the default scopes.
     *
     * @return array<string>
     */
    public function getDefaultScopes(): array
    {
        return $this->defaultScopes;
    }

    /**
     * Set the default scopes.
     *
     * @param array<string> $defaultScopes
     * @return $this
     */
    public function setDefaultScopes(array $defaultScopes): static
    {
        $this->defaultScopes = $defaultScopes;

        return $this;
    }

    /**
     * Validate the OAuth2 config.
     *
     * @return bool
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     */
    public function validate(): bool
    {
        if (empty($this->getClientId())) {
            throw new OAuthConfigValidationException('The Client ID is empty or has not been provided.');
        }

        if (empty($this->getClientSecret())) {
            throw new OAuthConfigValidationException('The Client Secret is empty or has not been provided.');
        }

        if (empty($this->getRedirectUri())) {
            throw new OAuthConfigValidationException('The Redirect URI is empty or has not been provided.');
        }

        return true;
    }
}
