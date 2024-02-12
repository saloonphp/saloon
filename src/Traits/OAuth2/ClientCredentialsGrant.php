<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use DateInterval;
use DateTimeImmutable;
use Saloon\Http\Request;
use Saloon\Http\Response;
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
     * @template TRequest of \Saloon\Http\Request
     *
     * @param array<string> $scopes
     * @param callable(TRequest): (void)|null $requestModifier
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
        $expiresAt = null;

        if (isset($responseData->expires_in) && is_numeric($responseData->expires_in)) {
            $expiresAt = (new DateTimeImmutable)->add(
                DateInterval::createFromDateString((int)$responseData->expires_in . ' seconds')
            );
        }

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
