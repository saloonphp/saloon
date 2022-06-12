<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class MockResponsePipe
{
    /**
     * @param MockClient $mockClient
     */
    public function __construct(protected MockClient $mockClient)
    {
        //
    }

    /**
     * Return the fake fulfilled response.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return \Closure
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException
     */
    public function __invoke(PendingSaloonRequest $pendingRequest)
    {
        $mockResponse = $this->mockClient->guessNextResponse($pendingRequest->getRequest());

        $pendingRequest->setEarlyResponse($mockResponse->toSaloonResponse($pendingRequest));
    }
}
