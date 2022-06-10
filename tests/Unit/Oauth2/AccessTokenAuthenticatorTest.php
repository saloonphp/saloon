<?php

use Carbon\CarbonImmutable;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;

it('can be serialized and unserialized', function () {
    $accessToken = 'access';
    $refreshToken = 'refresh';
    $expiresAt = CarbonImmutable::now();

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
    $expiresAt = CarbonImmutable::now()->subMinutes(5);

    $authenticator = new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);

    expect($authenticator->hasExpired())->toBeTrue();
    expect($authenticator->hasNotExpired())->toBeFalse();
});
