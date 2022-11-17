<?php declare(strict_types=1);

use Saloon\Helpers\Date;
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

    expect($authenticator->hasExpired())->toBeTrue();
    expect($authenticator->hasNotExpired())->toBeFalse();
});
