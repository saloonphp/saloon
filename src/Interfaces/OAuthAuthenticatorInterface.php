<?php

namespace Sammyjo20\Saloon\Interfaces;

use Carbon\CarbonInterface;

interface OAuthAuthenticatorInterface extends AuthenticatorInterface
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
     * @return CarbonInterface
     */
    public function getExpiresAt(): CarbonInterface;

    /**
     * @return bool
     */
    public function hasExpired(): bool;

    /**
     * @return bool
     */
    public function hasNotExpired(): bool;
}
