<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Carbon\CarbonInterface;
use Saloon\Http\SaloonConnector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Tests\Fixtures\Authenticators\CustomOAuthAuthenticator;

class CustomResponseOAuth2Connector extends SaloonConnector
{
    use AuthorizationCodeGrant;

    /**
     * @param string $greeting
     */
    public function __construct(protected string $greeting)
    {
        //
    }

    /**
     * Define the base URL.
     *
     * @return string
     */
    public function defineBaseUrl(): string
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
            ->setRedirectUri('https://my-app.saloon.dev/oauth/redirect');
    }

    /**
     * Define a custom OAuth2 authenticator.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param CarbonInterface $expiresAt
     * @return OAuthAuthenticator
     */
    protected function createOAuthAuthenticator(string $accessToken, string $refreshToken, CarbonInterface $expiresAt): OAuthAuthenticator
    {
        return new CustomOAuthAuthenticator($accessToken, $refreshToken, $expiresAt, $this->greeting);
    }
}
