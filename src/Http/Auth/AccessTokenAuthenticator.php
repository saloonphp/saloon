<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use DateTimeImmutable;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\OAuthAuthenticator;

class AccessTokenAuthenticator implements OAuthAuthenticator
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param \DateTimeImmutable $expiresAt
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public DateTimeImmutable $expiresAt,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Authorization', 'Bearer ' . $this->getAccessToken());
    }

    /**
     * Serialize the access token.
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this);
    }

    /**
     * Unserialize the access token.
     *
     * @param string $string
     * @return static
     */
    public static function unserialize(string $string): static
    {
        return unserialize($string, ['allowed_classes' => true]);
    }

    /**
     * Check if the access token has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Check if the access token has not expired.
     *
     * @return bool
     */
    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
