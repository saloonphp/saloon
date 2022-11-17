<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class UserRequestWithCustomResponse extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected string $method = 'GET';

    /**
     * Default Response
     *
     * @var string
     */
    protected string $response = UserResponse::class;

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
