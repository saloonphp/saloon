<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use DateTimeImmutable;

interface OAuthAuthenticator extends Authenticator
{
    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable;

    /**
     * @return bool
     */
    public function hasExpired(): bool;

    /**
     * @return bool
     */
    public function hasNotExpired(): bool;
}
