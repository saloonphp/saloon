<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use DateTimeImmutable;

interface OAuthAuthenticator extends Authenticator
{
    /**
     * Get the access token
     */
    public function getAccessToken(): string;

    /**
     * Get the refresh token
     */
    public function getRefreshToken(): ?string;

    /**
     * Get the expiry
     */
    public function getExpiresAt(): ?DateTimeImmutable;

    /**
     * Check if the authenticator has expired
     */
    public function hasExpired(): bool;

    /**
     * Check if the authenticator has not expired
     */
    public function hasNotExpired(): bool;

    /**
     * Check if the authenticator is refreshable
     */
    public function isRefreshable(): bool;

    /**
     * Check if the authenticator is not refreshable
     */
    public function isNotRefreshable(): bool;
}
