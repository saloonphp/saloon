<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Http\Fixture;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException;

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
     * @return Fixture|MockResponse
     * @throws SaloonInvalidConnectorException
     * @throws SaloonNoMockResponseFoundException
     */
    public function __invoke(PendingSaloonRequest $request)
    {
        // Todo: we'll probably have to move it out of here to support fixtures, i.e multiple middleware
        // Todo: Or support adding more middleware while it is processing?

        return $this->mockClient->guessNextResponse($request);
    }
}
