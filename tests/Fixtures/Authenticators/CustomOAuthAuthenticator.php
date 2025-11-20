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
        readonly public string             $accessToken,
        readonly public string             $greeting,
        readonly public ?string            $refreshToken = null,
        readonly public ?DateTimeImmutable $expiresAt = null,
    ) {
        //
    }

    
    public function getGreeting(): string
    {
        return $this->greeting;
    }
}
