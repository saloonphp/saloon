<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Traits\Plugins\HasJsonBody;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

class ReplaceHeaderRequest extends Request
{
    use HasJsonBody;

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
    protected string $connector = HeaderConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function defaultHeaders(): array
    {
        return [
            'X-Connector-Header' => 'Howdy',
        ];
    }

    public function defaultData(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
