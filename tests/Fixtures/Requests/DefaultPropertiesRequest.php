<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultPropertiesRequest extends SaloonRequest
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
    public function defineEndpoint(): string
    {
        return '/user';
    }

    protected function defaultHeaders(): array
    {
        return [
            'X-Favourite-Artist' => 'Luke Combs'
        ];
    }

    protected function defaultQueryParameters(): array
    {
        return [
            'format' => 'json'
        ];
    }

    protected function defaultData(): mixed
    {
        return [
            'song' => 'Call Me'
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'debug' => true,
        ];
    }
}
