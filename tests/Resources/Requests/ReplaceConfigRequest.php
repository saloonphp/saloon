<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Features\HasJsonBody;
use Sammyjo20\Saloon\Tests\Resources\Connectors\HeaderConnector;

class ReplaceConfigRequest extends SaloonRequest
{
    use HasJsonBody;

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
    protected ?string $connector = HeaderConnector::class;

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

    public function defaultData(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
