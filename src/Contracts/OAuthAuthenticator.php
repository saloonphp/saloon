<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use DateTimeImmutable;

interface OAuthAuthenticator extends Authenticator
{
    
    public function getAccessToken(): string;

    
    public function getRefreshToken(): ?string;

    
    public function getExpiresAt(): ?DateTimeImmutable;

    
    public function hasExpired(): bool;

    
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
