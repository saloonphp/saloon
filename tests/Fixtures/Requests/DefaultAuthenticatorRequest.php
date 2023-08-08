<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultAuthenticatorRequest extends Request
{
    use RequiresAuth;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Provide default authentication.
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
