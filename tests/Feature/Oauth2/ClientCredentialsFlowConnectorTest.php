<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Tests\Fixtures\Connectors\ClientCredentialsConnector;

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
});
