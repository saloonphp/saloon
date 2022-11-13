<?php declare(strict_types=1);

namespace Saloon\Contracts;

use Carbon\CarbonInterface;

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
