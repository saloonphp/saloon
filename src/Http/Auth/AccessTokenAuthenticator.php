<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use DateTimeImmutable;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\OAuthAuthenticator;

class AccessTokenAuthenticator implements OAuthAuthenticator
{
    /**
     * Constructor
     *
     * @param string $accessToken
     * @param string|null $refreshToken
     * @param \DateTimeImmutable|null $expiresAt
     */
    public function __construct(
        readonly public string             $accessToken,
        readonly public ?string            $refreshToken = null,
        readonly public ?DateTimeImmutable $expiresAt = null,
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
     * Check if the access token has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        if (is_null($this->expiresAt)) {
            return false;
        }

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
     * Get the access token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Get the refresh token
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get the expires at DateTime instance
     *
     * @return \DateTimeImmutable|null
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * Check if the authenticator is refreshable
     *
     * @return bool
     */
    public function isRefreshable(): bool
    {
        return isset($this->refreshToken);
    }

    /**
     * Check if the authenticator is not refreshable
     *
     * @return bool
     */
    public function isNotRefreshable(): bool
    {
        return ! $this->isRefreshable();
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
}
