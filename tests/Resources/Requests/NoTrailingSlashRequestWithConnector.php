<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\NoTrailingSlashConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TrailingSlashConnector;

class NoTrailingSlashRequestWithConnector extends SaloonRequest
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
    protected ?string $connector = NoTrailingSlashConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '';
    }
}
