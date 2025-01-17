<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\QueryBuilders\CustomQueryBuilder;

class CustomQueryBuilderRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     */
    protected string $connector = TestConnector::class;

    /**
     * The query builder.
     */
    protected string $queryBuilder = CustomQueryBuilder::class;

    /**
     * Constructor
     */
    public function __construct(readonly public string $endpoint = '/user')
    {
        //
    }

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    protected function defaultQuery(): array
    {
        return [
            'per_page' => 100,
        ];
    }
}
