<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

class ConfigRequest extends Request
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
    protected string $connector = HeaderConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function defaultConfig(): array
    {
        return [
            'debug' => false,
        ];
    }
}
