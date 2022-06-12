<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\InvalidStateException;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Authenticators\CustomOAuthAuthenticator;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\CustomResponseOAuth2Connector;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\OAuth2Connector;

test('you can get the redirect url from a connector', function () {
    $connector = new OAuth2Connector;

    expect($connector->getState())->toBeNull();

    $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], 'my-state');

    $state = $connector->getState();

    expect($state)->toEqual('my-state');

    expect($url)->toEqual(
        'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-1%20scope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state'
    );
});

test('you can provide default scopes that will be applied to every authorization url', function () {
    $connector = new OAuth2Connector;

    $connector->oauthConfig()->setDefaultScopes(['scope-3']);

    $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], 'my-state');

    expect($url)->toEqual(
        'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-3%20scope-1%20scope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state'
    );
});

test('default state is generated automatically with every authorization url if state is not defined', function () {
    $connector = new OAuth2Connector;

    $connector->oauthConfig()->setDefaultScopes(['scope-3']);

    expect($connector->getState())->toBeNull();

    $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2']);
    $state = $connector->getState();

    expect($state)->toBeString();

    expect(Str::endsWith($url, $state))->toBeTrue();
});

test('you can request a token from a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $authenticator = $connector->getAccessToken('code', false);

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);
    expect($authenticator->getAccessToken())->toEqual('access');
    expect($authenticator->getRefreshToken())->toEqual('refresh');
    expect($authenticator->getExpiresAt())->toBeInstanceOf(CarbonImmutable::class);
});

test('you can request the original response instead of the authenticator on the create tokens method', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $response = $connector->getAccessToken('code', null, null, true);

    expect($response)->toBeInstanceOf(SaloonResponse::class);
    expect($response->json())->toEqual(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600]);
});

test('it will throw an exception if state is invalid', function () {
    $connector = new OAuth2Connector;

    $state = 'secret';
    $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], $state);

    $connector->getAccessToken('code', 'invalid', $state);
})->throws(InvalidStateException::class, 'Invalid state.');

test('you can refresh a token from a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $authenticator = new AccessTokenAuthenticator('access', 'refresh', CarbonImmutable::now()->addSeconds(3600));

    $newAuthenticator = $connector->refreshAccessToken($authenticator);

    expect($newAuthenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);
    expect($newAuthenticator->getAccessToken())->toEqual('access-new');
    expect($newAuthenticator->getRefreshToken())->toEqual('refresh-new');
    expect($newAuthenticator->getExpiresAt())->toBeInstanceOf(CarbonImmutable::class);
});

test('you can request the original response instead of the authenticator on the refresh tokens method', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
    ]);

    $connector = new OAuth2Connector;

    $connector->withMockClient($mockClient);

    $authenticator = new AccessTokenAuthenticator('access', 'refresh', CarbonImmutable::now()->addSeconds(3600));

    $response = $connector->refreshAccessToken($authenticator, true);

    expect($response)->toBeInstanceOf(SaloonResponse::class);
    expect($response->json())->toEqual(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]);
});

test('you can get the user from an oauth connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['user' => 'Sam']),
    ]);

    $connector = new OAuth2Connector;
    $connector->withMockClient($mockClient);

    $accessToken = new AccessTokenAuthenticator('access', 'refresh', CarbonImmutable::now()->addSeconds(3600));

    $response = $connector->getUser($accessToken);

    expect($response)->toBeInstanceOf(SaloonResponse::class);

    $originalRequest = $response->getOriginalRequest();

    expect($originalRequest->getHeaders())->toEqual([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer access',
    ]);
});

test('you can customize the oauth authenticator', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
    ]);

    $customConnector = new CustomResponseOAuth2Connector('Howdy!');
    $customConnector->withMockClient($mockClient);

    $authenticator = $customConnector->getAccessToken('code');

    expect($authenticator)->toBeInstanceOf(CustomOAuthAuthenticator::class);
    expect($authenticator->getGreeting())->toEqual('Howdy!');
});
