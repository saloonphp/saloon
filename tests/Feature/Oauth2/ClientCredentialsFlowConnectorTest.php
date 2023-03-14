<?php

declare(strict_types=1);

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Tests\Fixtures\Connectors\ClientCredentialsConnector;
use Saloon\Tests\Fixtures\Connectors\NoConfigAuthCodeConnector;
use Saloon\Tests\Fixtures\Connectors\NoConfigClientCredentialsConnector;

test('you can get the authenticator from the connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $authenticator = $connector->getAccessToken();

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);
    expect($authenticator->getAccessToken())->toEqual('access');
    expect($authenticator->getRefreshToken())->toBeNull();
    expect($authenticator->isRefreshable())->toBeFalse();
    expect($authenticator->getExpiresAt())->toBeInstanceOf(DateTimeImmutable::class);

    $mockClient->assertSentCount(1);

    expect($mockClient->getLastPendingRequest()->body()->all())->toEqual([
        'grant_type' => 'client_credentials',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'scope' => '',
    ]);
});

test('you can get the response instead of the authenticator', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->getAccessToken(returnResponse: true);

    expect($response)->toBeInstanceOf(Response::class);

    expect($response->json())->toEqual([
        'access_token' => 'access',
        'expires_in' => 3600,
    ]);
});

test('you can tap into the token request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $authenticator = $connector->getAccessToken(requestModifier: function (Request $request) {
        $request->query()->add('yee', 'haw');
    });

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);
    expect($authenticator->getAccessToken())->toEqual('access');
    expect($authenticator->getRefreshToken())->toBeNull();
    expect($authenticator->isRefreshable())->toBeFalse();
    expect($authenticator->getExpiresAt())->toBeInstanceOf(DateTimeImmutable::class);

    $mockClient->assertSentCount(1);

    expect($mockClient->getLastPendingRequest()->query()->all())->toEqual(['yee' => 'haw']);
});

test('you can send scopes with the token request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing']);

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);

    $mockClient->assertSentCount(1);

    expect($mockClient->getLastPendingRequest()->body()->all())->toEqual([
        'grant_type' => 'client_credentials',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'scope' => 'offline_access clients billing',
    ]);
});

test('default scopes on the oauth config will be merged in with the scopes on the token request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $connector->oauthConfig()->setDefaultScopes([
        'compliance',
    ]);

    $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing']);

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);

    $mockClient->assertSentCount(1);

    expect($mockClient->getLastPendingRequest()->body()->all())->toEqual([
        'grant_type' => 'client_credentials',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'scope' => 'compliance offline_access clients billing',
    ]);
});

test('the scope separator can be customised', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new ClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $connector->oauthConfig()->setDefaultScopes([
        'compliance',
    ]);

    $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing'], '+');

    expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class);

    $mockClient->assertSentCount(1);

    expect($mockClient->getLastPendingRequest()->body()->all())->toEqual([
        'grant_type' => 'client_credentials',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'scope' => 'compliance+offline_access+clients+billing',
    ]);
});

test('if you attempt to use the client credentials flow without a client id it will throw an exception', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new NoConfigClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(OAuthConfigValidationException::class);
    $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

    $connector->getAccessToken();
});

test('if you attempt to use the client credentials flow without a secret it will throw an exception', function () {
    $mockClient = new MockClient([
        MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
    ]);

    $connector = new NoConfigClientCredentialsConnector;
    $connector->withMockClient($mockClient);

    $connector->oauthConfig()->setClientId('hello');

    $this->expectException(OAuthConfigValidationException::class);
    $this->expectExceptionMessage('The Client Secret is empty or has not been provided.');

    $connector->getAccessToken();
});
