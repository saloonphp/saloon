<?php

namespace Sammyjo20\Saloon\Http\Auth;

use Carbon\CarbonInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;

class AccessTokenAuthenticator implements AuthenticatorInterface
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param CarbonInterface $expiresAt
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public CarbonInterface $expiresAt,
    )
    {
        //
    }

    public function set(SaloonRequest $request): void
    {
        // TODO: Implement set() method.
    }

    /**
     * Check if the access token has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->expiresAt->isPast();
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
     * @return CarbonInterface
     */
    public function getExpiresAt(): CarbonInterface
    {
        return $this->expiresAt;
    }
}
