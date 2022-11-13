<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Traits\Plugins\HasTimeout;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class TimeoutRequest extends SaloonRequest
{
    use HasTimeout;

    protected int $connectTimeout = 1;

    protected int $requestTimeout = 2;

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
}
