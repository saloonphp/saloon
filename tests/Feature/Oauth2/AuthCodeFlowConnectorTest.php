<?php

use Carbon\CarbonImmutable;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\OAuth2Connector;

test('you can get the redirect url from a connector', function () {
    $connector = new OAuth2Connector;

    $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], 'my-state');

    expect($url)->toEqual(
        'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-1my-statescope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Foauth%2Fredirect'
    );
});

test('you can request a token from a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600])
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $authenticator = $connector->getAccessToken('code', false);

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);
    expect($authenticator->getAccessToken())->toEqual('access');
    expect($authenticator->getRefreshToken())->toEqual('refresh');
    expect($authenticator->getExpiresAt())->toBeInstanceOf(CarbonImmutable::class);
});

test('you can refresh a token from a connector', function () {

});

test('you can get the resource owner from a connector', function () {

});
