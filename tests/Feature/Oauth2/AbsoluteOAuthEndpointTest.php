<?php

declare(strict_types=1);

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Http\OAuth2\GetClientCredentialsTokenRequest;

class AbsoluteTokenOAuthConnector extends Connector
{
    use ClientCredentialsGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://api.service.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $config = OAuthConfig::make()
            ->setClientId('id')
            ->setClientSecret('secret')
            ->setTokenEndpoint('https://auth.external.com/oauth/token');
        $config->allowBaseUrlOverride = true;

        return $config;
    }
}

class AbsoluteTokenOAuthConnectorWithoutAllow extends Connector
{
    use ClientCredentialsGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://api.service.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId('id')
            ->setClientSecret('secret')
            ->setTokenEndpoint('https://auth.external.com/oauth/token');
    }
}

test('OAuth client credentials pending request uses absolute token URL when OAuthConfig allows base override', function () {
    $connector = new AbsoluteTokenOAuthConnector;
    $request = new GetClientCredentialsTokenRequest($connector->oauthConfig());

    $pending = $connector->createPendingRequest($request);

    expect($pending->getUrl())->toBe('https://auth.external.com/oauth/token');
});

test('OAuth client credentials throws when token endpoint is absolute and OAuthConfig does not allow override', function () {
    $connector = new AbsoluteTokenOAuthConnectorWithoutAllow;
    $request = new GetClientCredentialsTokenRequest($connector->oauthConfig());

    expect(fn () => $connector->createPendingRequest($request))
        ->toThrow(InvalidArgumentException::class);
});

test('request allowBaseUrlOverride false overrides OAuthConfig allow for token URL', function () {
    $connector = new AbsoluteTokenOAuthConnector;
    $request = new GetClientCredentialsTokenRequest($connector->oauthConfig());
    $request->allowBaseUrlOverride = false;

    expect(fn () => $connector->createPendingRequest($request))
        ->toThrow(InvalidArgumentException::class);
});

class AuthorizeUrlOAuthConnector extends Connector
{
    use AuthorizationCodeGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://api.app.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $config = OAuthConfig::make()
            ->setClientId('id')
            ->setClientSecret('secret')
            ->setRedirectUri('https://app.com/cb')
            ->setAuthorizeEndpoint('https://login.provider.com/authorize');
        $config->allowBaseUrlOverride = true;

        return $config;
    }
}

test('getAuthorizationUrl works with absolute authorize endpoint when OAuthConfig allows override', function () {
    $connector = new AuthorizeUrlOAuthConnector;
    $url = $connector->getAuthorizationUrl();

    expect($url)->toStartWith('https://login.provider.com/authorize');
});

test('getAuthorizationUrl works with absolute authorize when connector allows override only', function () {
    $connector = new class extends Connector {
        use AuthorizationCodeGrant;

        public function __construct()
        {
            $this->allowBaseUrlOverride = true;
        }

        public function resolveBaseUrl(): string
        {
            return 'https://api.app.com';
        }

        protected function defaultOauthConfig(): OAuthConfig
        {
            return OAuthConfig::make()
                ->setClientId('id')
                ->setClientSecret('secret')
                ->setRedirectUri('https://app.com/cb')
                ->setAuthorizeEndpoint('https://login.provider.com/authorize');
        }
    };

    $url = $connector->getAuthorizationUrl();

    expect($url)->toStartWith('https://login.provider.com/authorize');
});
