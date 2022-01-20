<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\NoTrailingSlashConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TrailingSlashConnector;

class NoTrailingSlashRequest extends SaloonRequest
{
    /**
     * Specify if Saloon should add a trailing slash to the base url.
     *
     * @var bool
     */
    public bool $addTrailingSlashToBaseUrl = false;

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
    protected ?string $connector = TrailingSlashConnector::class;

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
