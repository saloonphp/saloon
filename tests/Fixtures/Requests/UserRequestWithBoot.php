<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\WithBootConnector;

class UserRequestWithBoot extends SaloonRequest
{
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
    protected string $connector = WithBootConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function boot(SaloonRequest $request): void
    {
        $this->addHeader('X-Request-Boot-Header', 'Yee-haw!');
        $this->addHeader('X-Request-Boot-With-Data', $request->farewell);
    }

    /**
     * @param string $farewell
     */
    public function __construct(protected string $farewell = 'Ride on, cowboy.')
    {
        //
    }
}
