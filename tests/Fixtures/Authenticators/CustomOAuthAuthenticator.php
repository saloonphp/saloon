<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Authenticators;

use DateTimeImmutable;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class CustomOAuthAuthenticator extends AccessTokenAuthenticator
{
    /**
     * Constructor
     */
    public function __construct(
        public readonly string             $accessToken,
        public readonly string             $greeting,
        public readonly ?string            $refreshToken = null,
        public readonly ?DateTimeImmutable $expiresAt = null,
    ) {
        //
    }

    
    public function getGreeting(): string
    {
        return $this->greeting;
    }
}
