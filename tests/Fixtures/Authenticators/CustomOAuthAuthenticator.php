<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Authenticators;

use Carbon\CarbonInterface;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;

class CustomOAuthAuthenticator extends AccessTokenAuthenticator
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param CarbonInterface $expiresAt
     * @param string $greeting
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public CarbonInterface $expiresAt,
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
