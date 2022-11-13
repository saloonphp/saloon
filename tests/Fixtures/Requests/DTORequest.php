<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Http\Responses\SaloonResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DTORequest extends SaloonRequest
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

    /**
     * Cast to a User.
     *
     * @param SaloonResponse $response
     * @return object
     */
    public function createDtoFromResponse(SaloonResponse $response): object
    {
        return User::fromSaloon($response);
    }
}
