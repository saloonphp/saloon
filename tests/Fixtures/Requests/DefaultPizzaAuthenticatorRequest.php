<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;

class DefaultPizzaAuthenticatorRequest extends Request
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
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * @return Authenticator|null
     */
    public function defaultAuth(): ?Authenticator
    {
        return new PizzaAuthenticator('BBQ Chicken', 'Lemonade');
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
