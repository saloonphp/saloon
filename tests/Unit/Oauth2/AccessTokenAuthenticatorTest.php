<?php

declare(strict_types=1);

use Saloon\Tests\Helpers\Date;
use Saloon\Http\Auth\AccessTokenAuthenticator;

/**
 * Gadget class used to demonstrate insecure deserialization: when unserialize()
 * is called with allowed_classes => true, this class can be instantiated from
 * attacker-controlled data and __wakeup() will execute (object injection).
 */
class AccessTokenAuthenticatorTestGadget
{
    public static bool $wakeupExecuted = false;

    public function __wakeup(): void
    {
        self::$wakeupExecuted = true;
    }
}

it('rejects PHP serialized payloads and does not execute arbitrary code', function () {
    AccessTokenAuthenticatorTestGadget::$wakeupExecuted = false;

    $maliciousPayload = serialize(new AccessTokenAuthenticatorTestGadget);

    try {
        AccessTokenAuthenticator::unserialize($maliciousPayload);
    } catch (\JsonException|\InvalidArgumentException) {
        // Expected: non-JSON or malformed payload is rejected.
    }

    expect(AccessTokenAuthenticatorTestGadget::$wakeupExecuted)->toBeFalse();
});

it('throws when unserializing invalid or malformed JSON', function () {
    expect(fn () => AccessTokenAuthenticator::unserialize('not json'))
        ->toThrow(\JsonException::class);

    expect(fn () => AccessTokenAuthenticator::unserialize('{}'))
        ->toThrow(\InvalidArgumentException::class);
});

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

    expect($unserialized->getAccessToken())->toEqual($accessToken);
    expect($unserialized->getRefreshToken())->toEqual($refreshToken);
    expect($unserialized->getExpiresAt()?->getTimestamp())->toEqual($authenticator->getExpiresAt()?->getTimestamp());
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
