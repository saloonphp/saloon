<?php

declare(strict_types=1);

use Saloon\Config;
use Saloon\Tests\Helpers\Date;
use Saloon\Tests\Fixtures\Clock\FixedClock;
use Saloon\Http\Auth\AccessTokenAuthenticator;

afterEach(function () {
    Config::setClock(null);
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

test('it can use the global clock for expiry checks', function () {
    $expiresAt = new DateTimeImmutable('2026-01-01T01:00:00+00:00');

    Config::setClock(new FixedClock(new DateTimeImmutable('2026-01-01T02:00:00+00:00')));

    $authenticator = new AccessTokenAuthenticator('access', 'refresh', $expiresAt);

    expect($authenticator->hasExpired())->toBeTrue();
    expect($authenticator->hasNotExpired())->toBeFalse();
});
