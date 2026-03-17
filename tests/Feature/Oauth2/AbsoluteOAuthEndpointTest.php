<?php

declare(strict_types=1);

use Saloon\Http\OAuth2\GetClientCredentialsTokenRequest;
use Saloon\Tests\Fixtures\Connectors\AuthorizeUrlOAuthConnector;
use Saloon\Tests\Fixtures\Connectors\AbsoluteTokenOAuthConnector;
use Saloon\Tests\Fixtures\Connectors\AbsoluteTokenOAuthConnectorWithoutAllow;
use Saloon\Tests\Fixtures\Connectors\AuthorizeUrlOAuthConnectorWithConnectorAllowOnly;

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

test('getAuthorizationUrl works with absolute authorize endpoint when OAuthConfig allows override', function () {
    $connector = new AuthorizeUrlOAuthConnector;
    $url = $connector->getAuthorizationUrl();

    expect($url)->toStartWith('https://login.provider.com/authorize');
});

test('getAuthorizationUrl works with absolute authorize when connector allows override only', function () {
    $connector = new AuthorizeUrlOAuthConnectorWithConnectorAllowOnly;
    $url = $connector->getAuthorizationUrl();

    expect($url)->toStartWith('https://login.provider.com/authorize');
});
