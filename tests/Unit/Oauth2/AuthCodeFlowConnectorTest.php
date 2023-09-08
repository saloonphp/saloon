<?php

declare(strict_types=1);

use Saloon\Tests\Helpers\Date;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Tests\Fixtures\Connectors\OAuth2Connector;

test('the oauth 2 config class can be configured properly', function () {
    $connector = new OAuth2Connector;

    $config = $connector->oauthConfig();

    expect($config)->toBeInstanceOf(OAuthConfig::class);
    expect($config->getClientId())->toEqual('client-id');
    expect($config->getClientSecret())->toEqual('client-secret');
    expect($config->getRedirectUri())->toEqual('https://my-app.saloon.dev/auth/callback');
});

test('the oauth config is validated when generating an authorization url', function () {
    $connector = new OAuth2Connector;
    $connector->oauthConfig()->setClientId('');

    $connector->getAuthorizationUrl();
})->throws(OAuthConfigValidationException::class, 'The Client ID is empty or has not been provided.');

test('the oauth config is validated when creating access tokens', function () {
    $connector = new OAuth2Connector;
    $connector->oauthConfig()->setClientId('');

    $connector->getAccessToken('code');
})->throws(OAuthConfigValidationException::class, 'The Client ID is empty or has not been provided.');

test('the oauth config is validated when refreshing access tokens', function () {
    $connector = new OAuth2Connector;
    $connector->oauthConfig()->setClientId('');

    $connector->refreshAccessToken('');
})->throws(OAuthConfigValidationException::class, 'The Client ID is empty or has not been provided.');

test('the old refresh token is carried over if a response does not include a new refresh token', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access-new', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $authenticator = new AccessTokenAuthenticator('access', 'refresh-old', Date::now()->addSeconds(3600)->toDateTime());

    $newAuthenticator = $connector->refreshAccessToken($authenticator);

    expect($newAuthenticator->getRefreshToken())->toEqual('refresh-old');
});

test('the old refresh token is carried over if a response does not include a new refresh token and the refresh is a string', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access-new', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $newAuthenticator = $connector->refreshAccessToken('refresh-old');

    expect($newAuthenticator->getRefreshToken())->toEqual('refresh-old');
});
