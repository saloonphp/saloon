<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;

class MockRequest extends SaloonRequest
{
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

    /**
     * Default headers on the request
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [
            'X-Mock-Header' => 'Mocking',
        ];
    }

    /**
     * @return int[]
     */
    public function defaultConfig(): array
    {
        return [
            'timeout' => 5,
        ];
    }
}
