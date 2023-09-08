<?php

declare(strict_types=1);

namespace Saloon\Helpers\OAuth2;

use Closure;
use Saloon\Http\Request;
use Saloon\Traits\Makeable;
use Saloon\Exceptions\OAuthConfigValidationException;

/**
 * @method static static make()
 */
class OAuthConfig
{
    use Makeable;

    /**
     * The Client ID
     */
    protected string $clientId = '';

    /**
     * The Client Secret
     */
    protected string $clientSecret = '';

    /**
     * The Redirect URI
     */
    protected string $redirectUri = '';

    /**
     * The endpoint used for the authorization URL.
     */
    protected string $authorizeEndpoint = 'authorize';

    /**
     * The endpoint used to create and refresh tokens.
     */
    protected string $tokenEndpoint = 'token';

    /**
     * The endpoint used to retrieve user information.
     */
    protected string $userEndpoint = 'user';

    /**
     * Callable that modifies the OAuth requests
     *
     * @var \Closure(\Saloon\Http\Request): (void)|null
     */
    protected ?Closure $requestModifier = null;

    /**
     * The default scopes that will be applied to every authorization URL.
     *
     * @var array<string>
     */
    protected array $defaultScopes = [];

    /**
     * Get the Client ID
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Set the Client ID
     *
     * @return $this
     */
    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get the Client Secret
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Set the Client Secret
     *
     * @return $this
     */
    public function setClientSecret(string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get the Redirect URI
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * Set the Redirect URI
     *
     * @return $this
     */
    public function setRedirectUri(string $redirectUri): static
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get the authorization endpoint.
     */
    public function getAuthorizeEndpoint(): string
    {
        return $this->authorizeEndpoint;
    }

    /**
     * Set the authorization endpoint.
     *
     * @return $this
     */
    public function setAuthorizeEndpoint(string $authorizeEndpoint): static
    {
        $this->authorizeEndpoint = $authorizeEndpoint;

        return $this;
    }

    /**
     * Get the token endpoint.
     */
    public function getTokenEndpoint(): string
    {
        return $this->tokenEndpoint;
    }

    /**
     * Set the token endpoint.
     *
     * @return $this
     */
    public function setTokenEndpoint(string $tokenEndpoint): static
    {
        $this->tokenEndpoint = $tokenEndpoint;

        return $this;
    }

    /**
     * Get the user endpoint.
     */
    public function getUserEndpoint(): string
    {
        return $this->userEndpoint;
    }

    /**
     * Set the user endpoint.
     *
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
     * Set the request modifier callable which can be used to modify the request being sent
     *
     * @param callable(\Saloon\Http\Request): (void) $requestModifier
     * @return $this
     */
    public function setRequestModifier(callable $requestModifier): static
    {
        $this->requestModifier = $requestModifier(...);

        return $this;
    }

    /**
     * Invoke the OAuth2 config request modifier
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param TRequest $request
     * @return TRequest
     */
    public function invokeRequestModifier(Request $request): Request
    {
        $requestModifier = $this->requestModifier;

        if (is_null($requestModifier)) {
            return $request;
        }

        $requestModifier($request);

        return $request;
    }

    /**
     * Validate the OAuth2 config.
     *
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     */
    public function validate(bool $withRedirectUrl = true): bool
    {
        if (empty($this->getClientId())) {
            throw new OAuthConfigValidationException('The Client ID is empty or has not been provided.');
        }

        if (empty($this->getClientSecret())) {
            throw new OAuthConfigValidationException('The Client Secret is empty or has not been provided.');
        }

        if ($withRedirectUrl === true && empty($this->getRedirectUri())) {
            throw new OAuthConfigValidationException('The Redirect URI is empty or has not been provided.');
        }

        return true;
    }
}
