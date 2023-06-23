<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Request;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomOAuthUserRequest;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomAccessTokenRequest;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomRefreshTokenRequest;

class CustomRequestOAuth2Connector extends Connector
{
    use AuthorizationCodeGrant;

    /**
     * Define the base URL.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://oauth.saloon.dev';
    }

    /**
     * Define default Oauth config.
     *
     * @return OAuthConfig
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId('client-id')
            ->setClientSecret('client-secret')
            ->setRedirectUri('https://my-app.saloon.dev/auth/callback');
    }

    /**
     * Resolve the access token request
     *
     * @param string $code
     * @param OAuthConfig $oauthConfig
     * @return Request
     */
    protected function resolveAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new CustomAccessTokenRequest($code, $oauthConfig);
    }

    /**
     * Resolve the refresh token request
     *
     * @param OAuthConfig $oauthConfig
     * @param string $refreshToken
     * @return Request
     */
    protected function resolveRefreshTokenRequest(OAuthConfig $oauthConfig, string $refreshToken): Request
    {
        return new CustomRefreshTokenRequest($oauthConfig, $refreshToken);
    }

    /**
     * Resolve the user request
     *
     * @param OAuthConfig $oauthConfig
     * @return Request
     */
    protected function resolveUserRequest(OAuthConfig $oauthConfig): Request
    {
        return new CustomOAuthUserRequest($oauthConfig);
    }
}
