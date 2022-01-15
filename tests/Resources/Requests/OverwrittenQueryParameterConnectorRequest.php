<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\QueryParameterConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Traits\Features\HasQueryParams;

class OverwrittenQueryParameterConnectorRequest extends SaloonRequest
{
    use HasQueryParams;

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
    protected ?string $connector = QueryParameterConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function defaultQuery(): array
    {
        return [
            'sort' => 'date_of_birth',
        ];
    }
}


