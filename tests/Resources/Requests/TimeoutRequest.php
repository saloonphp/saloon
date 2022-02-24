<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Features\HasTimeout;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;

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
    protected ?string $method = Saloon::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = TestConnector::class;

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
