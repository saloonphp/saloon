<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Auth\RequiresAuth;
use Sammyjo20\Saloon\Http\Auth\TokenAuthenticator;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

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
     * @return AuthenticatorInterface|null
     */
    public function defaultAuth(): ?AuthenticatorInterface
    {
        return new TokenAuthenticator('yee-haw-request');
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
