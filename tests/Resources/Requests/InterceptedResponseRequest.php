<?php

namespace Sammyjo20\Saloon\Tests\Resources\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;

class InterceptedResponseRequest extends SaloonRequest
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
    protected ?string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/error';
    }

    /**
     * Always throw an error
     *
     * @param SaloonRequest $request
     * @param SaloonResponse $responseInstance
     * @return SaloonResponse
     */
    public function interceptResponse(SaloonRequest $request, SaloonResponse $responseInstance): SaloonResponse
    {
        $responseInstance->throw();

        return $responseInstance;
    }
}


