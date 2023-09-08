<?php

declare(strict_types=1);

use Saloon\Tests\Helpers\Date;
use Saloon\Http\Auth\AccessTokenAuthenticator;

it('can be serialized and unserialized', function () {
    $accessToken = 'access';
    $refreshToken = 'refresh';
    $expiresAt = Date::now()->toDateTime();

    $authenticator = new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);

    expect($authenticator->getAccessToken())->toEqual($accessToken);
    expect($authenticator->getRefreshToken())->toEqual($refreshToken);
    expect($authenticator->getExpiresAt())->toEqual($expiresAt);

    $serialized = $authenticator->serialize();

    expect($serialized)->toBeString();

    $unserialized = AccessTokenAuthenticator::unserialize($serialized);

    expect($unserialized)->toEqual($authenticator);
});

it('can return if it has expired or not', function () {
    $accessToken = 'access';
    $refreshToken = 'refresh';
    $expiresAt = Date::now()->subMinutes(5)->toDateTime();

    $authenticator = new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);

    expect($authenticator->isRefreshable())->toBeTrue();
    expect($authenticator->isNotRefreshable())->toBeFalse();
    expect($authenticator->hasExpired())->toBeTrue();
    expect($authenticator->hasNotExpired())->toBeFalse();
});

test('can be constructed without a refresh token or expiry', function () {
    $authenticator = new AccessTokenAuthenticator('access');

    expect($authenticator->getAccessToken())->toEqual('access');
    expect($authenticator->getRefreshToken())->toBeNull();
    expect($authenticator->getExpiresAt())->toBeNull();
    expect($authenticator->isRefreshable())->toBeFalse();
    expect($authenticator->isNotRefreshable())->toBeTrue();
});

test('can be constructed with just an access token and expiry', function () {
    $expiresAt = Date::now()->subMinutes(5)->toDateTime();

    $authenticator = new AccessTokenAuthenticator('access', null, $expiresAt);

    expect($authenticator->hasExpired())->toBeTrue();
    expect($authenticator->hasNotExpired())->toBeFalse();
});

test('it allows expires_in to be optional', function () {
    $authenticator = new AccessTokenAuthenticator('access', 'refresh', null);

    expect($authenticator->getExpiresAt())->toBeNull();
    expect($authenticator->isRefreshable())->toBeTrue();
    expect($authenticator->isNotRefreshable())->toBeFalse();
});
