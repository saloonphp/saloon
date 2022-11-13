<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Tests\Fixtures\Plugins\HasTestHandler;
use Saloon\Tests\Fixtures\Connectors\HandlerConnector;

class UserWithTestHandlerConnectorRequest extends SaloonRequest
{
    use HasTestHandler;

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
    protected string $connector = HandlerConnector::class;

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
