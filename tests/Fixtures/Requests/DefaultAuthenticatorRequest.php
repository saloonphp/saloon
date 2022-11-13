<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultAuthenticatorRequest extends SaloonRequest
{
    use RequiresAuth;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected string $method = 'GET';

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    /**
     * Provide default authentication.
     *
     * @return Authenticator|null
     */
    protected function defaultAuth(): ?Authenticator
    {
        return new TokenAuthenticator('yee-haw-request');
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
