<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use DateTimeImmutable;
use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Tests\Fixtures\Authenticators\CustomOAuthAuthenticator;

class CustomResponseOAuth2Connector extends Connector
{
    use AuthorizationCodeGrant;


    public function __construct(protected string $greeting)
    {
        //
    }

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
            ->setRedirectUri('https://my-app.saloon.dev/oauth/redirect');
    }

    /**
     * Create the OAuth authenticator
     */
    protected function createOAuthAuthenticator(string $accessToken, ?string $refreshToken = null, ?DateTimeImmutable $expiresAt = null): OAuthAuthenticator
    {
        return new CustomOAuthAuthenticator($accessToken, $this->greeting,  $refreshToken, $expiresAt);
    }
}
