<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Contracts\Response;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Data\UserWithResponse;

class DTOPropertyRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * Response Data Object
     *
     * @var string
     */
    protected string $responseDataObject = UserWithResponse::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
