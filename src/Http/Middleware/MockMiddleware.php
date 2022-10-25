<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class MockMiddleware
{
    /**
     * Constructor
     *
     * @param MockClient $mockClient
     */
    public function __construct(protected MockClient $mockClient)
    {
        //
    }

    /**
     * Guess a mock response
     *
     * @param PendingSaloonRequest $request
     * @return void
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException
     */
    public function __invoke(PendingSaloonRequest $request)
    {
        $mockResponse = $this->mockClient->guessNextResponse($request);

        // Todo: we'll probably have to move it out of here to support fixtures, i.e multiple middleware
        // Todo: Or support adding more middleware while it is processing?

        return $mockResponse;
    }
}
