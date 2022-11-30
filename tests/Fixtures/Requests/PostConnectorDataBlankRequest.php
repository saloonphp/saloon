<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Contracts\Body\WithBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Tests\Fixtures\Connectors\PostJsonConnector;

class PostConnectorDataBlankRequest extends Request implements WithBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected string $method = 'POST';

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = PostJsonConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
