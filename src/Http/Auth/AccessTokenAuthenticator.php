<?php

namespace Sammyjo20\Saloon\Http\Auth;

use Carbon\CarbonInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\OAuthAuthenticatorInterface;

class AccessTokenAuthenticator implements OAuthAuthenticatorInterface
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
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function set(SaloonRequest $request): void
    {
        $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
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
