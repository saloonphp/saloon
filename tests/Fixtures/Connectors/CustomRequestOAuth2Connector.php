<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
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
     */
    public function resolveBaseUrl(): string
    {
        return 'https://oauth.saloon.dev';
    }

    /**
     * Define default Oauth config.
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
     */
    protected function resolveAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new CustomAccessTokenRequest($code, $oauthConfig);
    }

    /**
     * Resolve the refresh token request
     */
    protected function resolveRefreshTokenRequest(OAuthConfig $oauthConfig, string $refreshToken): Request
    {
        return new CustomRefreshTokenRequest($oauthConfig, $refreshToken);
    }

    /**
     * Resolve the user request
     */
    protected function resolveUserRequest(OAuthConfig $oauthConfig): Request
    {
        return new CustomOAuthUserRequest($oauthConfig);
    }
}
