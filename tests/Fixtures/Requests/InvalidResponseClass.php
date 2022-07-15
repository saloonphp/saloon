<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Responses\UserResponseNoExtendSaloonResponse;

class InvalidResponseClass extends SaloonRequest
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'GET';

    protected ?string $response = UserResponseNoExtendSaloonResponse::class;

    /**
     * The connector.
     *
     * @var string
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
}
