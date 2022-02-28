<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\InterceptedConnector;

class InterceptedConnectorErrorRequest extends SaloonRequest
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
    protected ?string $connector = InterceptedConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/error';
    }

    public function boot(SaloonRequest $request): void
    {
        $this->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            $response->throw();

            return $response;
        });
    }
}
