<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Data\UserWithResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DTOWithResponseRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }

    /**
     * Cast to a User.
     */
    public function createDtoFromResponse(Response $response): object
    {
        return UserWithResponse::fromResponse($response);
    }
}
