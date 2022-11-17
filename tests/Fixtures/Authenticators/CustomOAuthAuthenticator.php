<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Authenticators;

use DateTimeImmutable;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class CustomOAuthAuthenticator extends AccessTokenAuthenticator
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param DateTimeImmutable $expiresAt
     * @param string $greeting
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public DateTimeImmutable $expiresAt,
        public string $greeting,
    ) {
        //
    }

    /**
     * @return string
     */
    public function getGreeting(): string
    {
        return $this->greeting;
    }
}
