<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

class UserRequest extends SaloonRequest
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
     * @param int|null $userId
     * @param int|null $groupId
     */
    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }

    /**
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }
}
