<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use DateTimeImmutable;
use Saloon\Helpers\Date;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\OAuth2\GetClientCredentialsTokenRequest;

trait ClientCredentialsGrant
{
    use HasOAuthConfig;

    /**
     * Get the access token
     *
     * @template TRequest of \Saloon\Contracts\Request
     *
     * @param array<string> $scopes
     * @param string $scopeSeparator
     * @param bool $returnResponse
     * @param callable(TRequest): (void)|null $requestModifier
     * @return \Saloon\Contracts\OAuthAuthenticator|\Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     * @throws \Throwable
     */
    public function getAccessToken(array $scopes = [], string $scopeSeparator = ' ', bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate(withRedirectUrl: false);

        $request = $this->resolveAccessTokenRequest($this->oauthConfig(), $scopes, $scopeSeparator);

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response);
    }

    /**
     * Create the OAuthAuthenticator from a response.
     */
    protected function createOAuthAuthenticatorFromResponse(Response $response): OAuthAuthenticator
    {
        $responseData = $response->object();

        $accessToken = $responseData->access_token;
        $expiresAt = isset($responseData->expires_in) && is_numeric($responseData->expires_in)
            ? Date::now()->addSeconds((int) $responseData->expires_in)->toDateTime()
            : null;

        return $this->createOAuthAuthenticator($accessToken, $expiresAt);
    }

    /**
     * Create the authenticator.
     */
    protected function createOAuthAuthenticator(string $accessToken, ?DateTimeImmutable $expiresAt = null): OAuthAuthenticator
    {
        return new AccessTokenAuthenticator($accessToken, null, $expiresAt);
    }

    /**
     * Resolve the access token request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], string $scopeSeparator = ' '): Request
    {
        return new GetClientCredentialsTokenRequest($oauthConfig, $scopes, $scopeSeparator);
    }
}
